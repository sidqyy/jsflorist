<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ApiTokenModel;
use App\Models\MemberModel;
use App\Models\PasswordResetModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class MemberAuthController extends BaseController
{
    protected MemberModel $memberModel;
    protected ApiTokenModel $apiTokenModel;
    protected PasswordResetModel $passwordResetModel;
    protected $db;

    public function __construct()
    {
        $this->memberModel = new MemberModel();
        $this->apiTokenModel = new ApiTokenModel();
        $this->passwordResetModel = new PasswordResetModel();
        $this->db = Database::connect();
    }

    public function register(): ResponseInterface
    {
        $payload = $this->getJsonPayload();

        $username = trim((string) ($payload['username'] ?? ''));
        $email = trim((string) ($payload['email'] ?? ''));
        $password = (string) ($payload['password'] ?? '');
        $nomorHp = trim((string) ($payload['nomor_hp'] ?? ''));
        $birthDate = trim((string) ($payload['birth_date'] ?? ''));

        if ($username === '' || $email === '' || $password === '' || $birthDate === '') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
            ->setJSON(['message' => 'username, email, password, dan birth_date wajib diisi']);
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'Format email tidak valid']);
        }

        $date = \DateTime::createFromFormat('Y-m-d', $birthDate);
        $errors = \DateTime::getLastErrors();
        if (!$date || ($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'Format birth_date harus Y-m-d']);
        }

        $passwordError = $this->validatePasswordStrength($password);
        if ($passwordError !== null) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => $passwordError]);
        }

        $usersTable = $this->db->table('users');

        $emailExists = (int) $usersTable->where('email', $email)->countAllResults();
        if ($emailExists > 0) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                ->setJSON(['message' => 'Email sudah terdaftar']);
        }

        $usernameExists = (int) $usersTable->where('username', $username)->countAllResults();
        if ($usernameExists > 0) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                ->setJSON(['message' => 'Username sudah terdaftar']);
        }

        $now = date('Y-m-d H:i:s');
        $usersTable->insert([
            'username' => $username,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'nomor_hp' => $nomorHp ?: null,
            'birth_date' => $birthDate,
            'tanggal_daftar' => $now,
            'last_login' => null,
        ]);

        $userId = (int) $this->db->insertID();
        if ($userId <= 0) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON(['message' => 'Gagal membuat akun']);
        }

        $member = $this->createMemberForUser($userId);
        $token = $this->issueToken($userId);

        return $this->response->setJSON([
            'user' => [
                'user_id' => $userId,
                'username' => $username,
                'email' => $email,
                'nomor_hp' => $nomorHp,
            ],
            'member' => $member,
            'token' => $token,
        ]);
    }

    public function login(): ResponseInterface
    {
        $payload = $this->getJsonPayload();

        $identifier = trim((string) ($payload['email'] ?? $payload['username'] ?? ''));
        $password = (string) ($payload['password'] ?? '');

        if ($identifier === '' || $password === '') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'email/username dan password wajib diisi']);
        }

        $usersTable = $this->db->table('users');
        $user = $usersTable
            ->groupStart()
                ->where('email', $identifier)
                ->orWhere('username', $identifier)
            ->groupEnd()
            ->get()
            ->getRowArray();

        if (!$user || empty($user['password_hash']) || !password_verify($password, $user['password_hash'])) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(['message' => 'Login gagal, periksa kembali data Anda']);
        }

        $now = date('Y-m-d H:i:s');
        $usersTable->where('user_id', $user['user_id'])->update(['last_login' => $now]);

        $member = $this->memberModel->findByUserId((int) $user['user_id'])
            ?? $this->createMemberForUser((int) $user['user_id']);

        $token = $this->issueToken((int) $user['user_id']);

        return $this->response->setJSON([
            'user' => [
                'user_id' => (int) $user['user_id'],
                'username' => $user['username'],
                'email' => $user['email'],
                'nomor_hp' => $user['nomor_hp'] ?? null,
            ],
            'member' => $member,
            'token' => $token,
        ]);
    }

    public function logout(): ResponseInterface
    {
        $token = $this->getBearerToken();
        if ($token === null) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'Token tidak ditemukan']);
        }

        $tokenHash = hash('sha256', $token);
        $now = date('Y-m-d H:i:s');

        $this->apiTokenModel
            ->where('token_hash', $tokenHash)
            ->set(['revoked_at' => $now])
            ->update();

        return $this->response->setJSON(['message' => 'Logout berhasil']);
    }

    public function requestPasswordReset(): ResponseInterface
    {
        $payload = $this->getJsonPayload();
        $email = trim((string) ($payload['email'] ?? ''));

        if ($email === '') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'Email wajib diisi']);
        }

        $user = $this->db->table('users')->where('email', $email)->get()->getRowArray();
        if (!$user) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['message' => 'Email tidak ditemukan']);
        }

        $otp = (string) random_int(100000, 999999);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));
        $now = date('Y-m-d H:i:s');

        $resetId = $this->passwordResetModel->insert([
            'user_id' => (int) $user['user_id'],
            'otp_hash' => hash('sha256', $otp),
            'expires_at' => $expiresAt,
            'used_at' => null,
            'created_at' => $now,
        ], true);

        $emailService = service('email');
        $emailService->setTo($email);
        $emailService->setSubject('Kode OTP Reset Password');
        $emailService->setMessage($this->buildOtpEmailBody($otp, $expiresAt));

        if (!$emailService->send()) {
            if ($resetId) {
                $this->passwordResetModel->delete($resetId);
            }

            return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON(['message' => 'Gagal mengirim OTP ke email']);
        }

        return $this->response->setJSON(['message' => 'OTP reset password sudah dikirim ke email']);
    }

    public function resetPassword(): ResponseInterface
    {
        $payload = $this->getJsonPayload();
        $email = trim((string) ($payload['email'] ?? ''));
        $otp = trim((string) ($payload['otp'] ?? ''));
        $newPassword = (string) ($payload['new_password'] ?? '');

        if ($email === '' || $otp === '' || $newPassword === '') {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'email, otp, dan new_password wajib diisi']);
        }

        $passwordError = $this->validatePasswordStrength($newPassword);
        if ($passwordError !== null) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => $passwordError]);
        }

        $user = $this->db->table('users')->where('email', $email)->get()->getRowArray();
        if (!$user) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['message' => 'Email tidak ditemukan']);
        }

        $otpHash = hash('sha256', $otp);
        $now = date('Y-m-d H:i:s');

        $reset = $this->passwordResetModel
            ->where('user_id', (int) $user['user_id'])
            ->where('otp_hash', $otpHash)
            ->where('used_at', null)
            ->where('expires_at >=', $now)
            ->orderBy('id', 'DESC')
            ->first();

        if (!$reset) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(['message' => 'OTP tidak valid atau kadaluarsa']);
        }

        $this->db->table('users')->where('user_id', $user['user_id'])->update([
            'password_hash' => password_hash($newPassword, PASSWORD_DEFAULT),
        ]);

        $this->passwordResetModel->update($reset['id'], ['used_at' => $now]);

        return $this->response->setJSON(['message' => 'Password berhasil diperbarui']);
    }

    protected function getJsonPayload(): array
    {
        $json = $this->request->getJSON(true);
        if (is_array($json)) {
            return $json;
        }

        return $this->request->getPost() ?? [];
    }

    protected function createMemberForUser(int $userId): array
    {
        $now = date('Y-m-d H:i:s');
        $memberId = $this->memberModel->insert([
            'user_id' => $userId,
            'member_code' => $this->generateMemberCode($userId),
            'tier' => 'regular',
            'points_balance' => 0,
            'total_points_earned' => 0,
            'total_points_redeemed' => 0,
            'status' => 1,
            'joined_at' => $now,
            'updated_at' => $now,
        ], true);

        return $this->memberModel->find($memberId);
    }

    protected function generateMemberCode(int $userId): string
    {
        $seed = strtoupper(substr(md5($userId . microtime(true)), 0, 6));
        return 'MBR' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT) . $seed;
    }

    protected function issueToken(int $userId): string
    {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+30 days'));

        $this->apiTokenModel->insert([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'last_used_at' => null,
            'created_at' => date('Y-m-d H:i:s'),
            'revoked_at' => null,
        ]);

        return $token;
    }

    protected function getBearerToken(): ?string
    {
        $header = $this->request->getHeaderLine('Authorization');
        if (!$header) {
            return null;
        }

        if (stripos($header, 'Bearer ') !== 0) {
            return null;
        }

        return trim(substr($header, 7));
    }

    protected function validatePasswordStrength(string $password): ?string
    {
        if (strlen($password) < 8) {
            return 'Password minimal 8 karakter.';
        }

        if (!preg_match('/[A-Z]/', $password)) {
            return 'Password harus mengandung huruf besar.';
        }

        if (!preg_match('/[a-z]/', $password)) {
            return 'Password harus mengandung huruf kecil.';
        }

        if (!preg_match('/\d/', $password)) {
            return 'Password harus mengandung angka.';
        }

        return null;
    }

    protected function buildOtpEmailBody(string $otp, string $expiresAt): string
    {
        return "Kode OTP reset password Anda: {$otp}\n" .
            "Berlaku sampai: {$expiresAt}\n" .
            "Jika Anda tidak meminta reset password, abaikan email ini.";
    }
}
