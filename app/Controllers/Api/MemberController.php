<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MemberModel;
use App\Models\MemberPointModel;
use App\Models\MemberVoucherModel;
use App\Models\MemberGameModel;
use App\Models\MemberGameLogModel;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\ApiTokenModel;
use App\Models\VoucherModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class MemberController extends BaseController
{
    protected MemberModel $memberModel;
    protected MemberPointModel $memberPointModel;
    protected MemberVoucherModel $memberVoucherModel;
    protected MemberGameModel $memberGameModel;
    protected MemberGameLogModel $memberGameLogModel;
    protected OrderModel $orderModel;
    protected OrderItemModel $orderItemModel;
    protected ApiTokenModel $apiTokenModel;
    protected VoucherModel $voucherModel;
    protected $db;

    public function __construct()
    {
        $this->memberModel = new MemberModel();
        $this->memberPointModel = new MemberPointModel();
        $this->memberVoucherModel = new MemberVoucherModel();
        $this->memberGameModel = new MemberGameModel();
        $this->memberGameLogModel = new MemberGameLogModel();
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->apiTokenModel = new ApiTokenModel();
        $this->voucherModel = new VoucherModel();
        $this->db = Database::connect();
    }

    public function userProfile(): ResponseInterface
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(['message' => 'Unauthorized']);
        }

        $user = $this->db->table('users')
            ->select('user_id, username, email, nomor_hp, birth_date, profile_photo, tanggal_daftar, last_login')
            ->where('user_id', $userId)
            ->get()
            ->getRowArray();

        if (!$user) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['message' => 'User tidak ditemukan']);
        }

        $member = $this->memberModel->findByUserId((int) $userId);
        $user['profile_photo_url'] = !empty($user['profile_photo'])
            ? base_url($user['profile_photo'])
            : null;

        $user['member'] = $member ? [
            'member_id' => $member['member_id'],
            'member_code' => $member['member_code'],
            'tier' => $member['tier'],
            'points_balance' => (int) $member['points_balance'],
            'total_points_earned' => (int) $member['total_points_earned'],
            'total_points_redeemed' => (int) $member['total_points_redeemed'],
        ] : null;

        return $this->response->setJSON(['user' => $user]);
    }

    public function claimVoucher(): ResponseInterface
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(['message' => 'Unauthorized']);
        }

        $member = $this->memberModel->findByUserId((int) $userId);
        if (!$member) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'Member tidak ditemukan']);
        }

        $payload = $this->getJsonPayload();
        $voucherId = (int) ($payload['voucher_id'] ?? 0);

        if ($voucherId <= 0) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'voucher_id wajib diisi']);
        }

        $now = date('Y-m-d H:i:s');
        $voucher = $this->voucherModel
            ->where('id', $voucherId)
            ->where('is_active', 1)
            ->groupStart()
                ->where('expires_at', null)
                ->orWhere('expires_at >=', $now)
            ->groupEnd()
            ->first();

        if (!$voucher) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['message' => 'Voucher tidak ditemukan atau tidak aktif']);
        }

        if (!empty($voucher['usage_limit']) && (int) $voucher['used_count'] >= (int) $voucher['usage_limit']) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'Voucher sudah habis digunakan']);
        }

        $pointsCost = (int) ($voucher['points_cost'] ?? 0);
        if ($pointsCost <= 0) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'Voucher belum memiliki biaya poin']);
        }

        $alreadyClaimed = $this->memberVoucherModel
            ->where('member_id', $member['member_id'])
            ->where('voucher_code', $voucher['code'])
            ->first();

        if ($alreadyClaimed) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'Voucher ini sudah pernah diklaim']);
        }

        if ((int) $member['points_balance'] < $pointsCost) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'Poin tidak mencukupi']);
        }

        $this->db->transBegin();
        try {
            $this->memberVoucherModel->insert([
                'member_id' => $member['member_id'],
                'voucher_code' => $voucher['code'],
                'voucher_name' => $voucher['name'],
                'discount_type' => $voucher['discount_type'],
                'discount_value' => $voucher['discount_value'],
                'min_amount' => $voucher['min_amount'],
                'max_amount' => $voucher['max_amount'],
                'expires_at' => $voucher['expires_at'],
                'status' => 'active',
                'created_at' => $now,
                'used_at' => null,
                'order_id' => null,
            ]);

            $this->memberPointModel->insert([
                'member_id' => $member['member_id'],
                'points' => $pointsCost,
                'type' => 'redeem',
                'source' => 'voucher',
                'reference_id' => 'voucher:' . $voucher['code'],
                'note' => 'Tukar poin ke voucher',
                'created_at' => $now,
            ]);

            $this->memberModel->update($member['member_id'], [
                'points_balance' => (int) $member['points_balance'] - $pointsCost,
                'total_points_redeemed' => (int) $member['total_points_redeemed'] + $pointsCost,
                'updated_at' => $now,
            ]);

            $this->voucherModel->update($voucher['id'], [
                'used_count' => (int) ($voucher['used_count'] ?? 0) + 1,
            ]);

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return $this->response->setStatusCode(ResponseInterface::HTTP_INTERNAL_SERVER_ERROR)
                ->setJSON(['message' => 'Gagal klaim voucher']);
        }

        return $this->response->setJSON([
            'voucher_id' => (int) $voucher['id'],
            'voucher_code' => $voucher['code'],
            'voucher_name' => $voucher['name'],
            'points_balance' => (int) $member['points_balance'] - $pointsCost,
        ]);
    }

    public function updateProfile(): ResponseInterface
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(['message' => 'Unauthorized']);
        }

        $payload = $this->getJsonPayload();
        $username = trim((string) ($payload['username'] ?? ''));
        $nomorHp = trim((string) ($payload['nomor_hp'] ?? ''));
        $birthDate = trim((string) ($payload['birth_date'] ?? ''));

        $updates = [];

        if ($username !== '') {
            $exists = (int) $this->db->table('users')
                ->where('username', $username)
                ->where('user_id !=', $userId)
                ->countAllResults();
            if ($exists > 0) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_CONFLICT)
                    ->setJSON(['message' => 'Username sudah digunakan']);
            }
            $updates['username'] = $username;
        }

        if ($nomorHp !== '') {
            $updates['nomor_hp'] = $nomorHp;
        }

        if ($birthDate !== '') {
            $date = \DateTime::createFromFormat('Y-m-d', $birthDate);
            $errors = \DateTime::getLastErrors();
            if (!$date || ($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                    ->setJSON(['message' => 'Format birth_date harus Y-m-d']);
            }
            $updates['birth_date'] = $birthDate;
        }

        if (empty($updates)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'Tidak ada data untuk diupdate']);
        }

        $this->db->table('users')->where('user_id', $userId)->update($updates);

        return $this->userProfile();
    }

    public function uploadProfilePhoto(): ResponseInterface
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(['message' => 'Unauthorized']);
        }

        $file = $this->request->getFile('photo');
        if (!$file || !$file->isValid()) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'File foto tidak valid']);
        }

        $mime = $file->getMimeType();
        $allowed = ['image/jpeg', 'image/png', 'image/webp'];
        if (!in_array($mime, $allowed, true)) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'Format foto harus JPG, PNG, atau WEBP']);
        }

        $uploadDir = FCPATH . 'uploads/profile';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newName = 'user_' . $userId . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $file->getExtension();
        $file->move($uploadDir, $newName);

        $relativePath = 'uploads/profile/' . $newName;
        $this->db->table('users')->where('user_id', $userId)->update([
            'profile_photo' => $relativePath,
        ]);

        return $this->response->setJSON([
            'profile_photo' => $relativePath,
            'profile_photo_url' => base_url($relativePath),
        ]);
    }

    public function profile(): ResponseInterface
    {
        $userId = $this->getAuthenticatedUserId() ?? (int) ($this->request->getGet('user_id') ?? $this->request->getPost('user_id'));
        $memberId = (int) ($this->request->getGet('member_id') ?? $this->request->getPost('member_id'));

        $member = $this->resolveMember($userId, $memberId);
        if (!$member) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'user_id or member_id is required']);
        }

        return $this->response->setJSON(['member' => $member]);
    }

    public function points(): ResponseInterface
    {
        $userId = $this->getAuthenticatedUserId() ?? (int) ($this->request->getGet('user_id') ?? $this->request->getPost('user_id'));
        $memberId = (int) ($this->request->getGet('member_id') ?? $this->request->getPost('member_id'));

        $member = $this->resolveMember($userId, $memberId);
        if (!$member) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'user_id or member_id is required']);
        }

        $history = $this->memberPointModel
            ->where('member_id', $member['member_id'])
            ->orderBy('created_at', 'DESC')
            ->findAll(100);

        return $this->response->setJSON([
            'member' => [
                'member_id' => $member['member_id'],
                'points_balance' => (int) $member['points_balance'],
                'total_points_earned' => (int) $member['total_points_earned'],
                'total_points_redeemed' => (int) $member['total_points_redeemed'],
            ],
            'history' => $history,
        ]);
    }

    public function vouchers(): ResponseInterface
    {
        $userId = $this->getAuthenticatedUserId() ?? (int) ($this->request->getGet('user_id') ?? $this->request->getPost('user_id'));
        $memberId = (int) ($this->request->getGet('member_id') ?? $this->request->getPost('member_id'));

        $member = $this->resolveMember($userId, $memberId);
        if (!$member) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'user_id or member_id is required']);
        }

        $now = date('Y-m-d H:i:s');
        $vouchers = $this->memberVoucherModel
            ->where('member_id', $member['member_id'])
            ->where('status', 'active')
            ->groupStart()
                ->where('expires_at', null)
                ->orWhere('expires_at >=', $now)
            ->groupEnd()
            ->orderBy('expires_at', 'ASC')
            ->findAll();

        return $this->response->setJSON(['vouchers' => $vouchers]);
    }

    public function voucherCatalog(): ResponseInterface
    {
        $now = date('Y-m-d H:i:s');
        $vouchers = $this->voucherModel
            ->where('is_active', 1)
            ->groupStart()
                ->where('expires_at', null)
                ->orWhere('expires_at >=', $now)
            ->groupEnd()
            ->orderBy('created_at', 'DESC')
            ->findAll();

        return $this->response->setJSON(['vouchers' => $vouchers]);
    }

    public function games(): ResponseInterface
    {
        $games = $this->memberGameModel
            ->where('is_active', 1)
            ->orderBy('name', 'ASC')
            ->findAll();

        return $this->response->setJSON(['games' => $games]);
    }

    public function play(): ResponseInterface
    {
        $userId = $this->getAuthenticatedUserId() ?? (int) ($this->request->getPost('user_id') ?? 0);
        $memberId = (int) ($this->request->getPost('member_id') ?? 0);
        $gameId = (int) ($this->request->getPost('game_id') ?? 0);
        $result = (string) ($this->request->getPost('result') ?? 'win');
        $metadata = $this->request->getPost('metadata');

        if ($gameId <= 0) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'game_id is required']);
        }

        $member = $this->resolveMember($userId, $memberId);
        if (!$member) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'user_id or member_id is required']);
        }

        $game = $this->memberGameModel->find($gameId);
        if (!$game || (int) $game['is_active'] !== 1) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['message' => 'game not found']);
        }

        $dailyLimit = (int) ($game['daily_limit'] ?? 0);
        if ($dailyLimit > 0) {
            $todayStart = date('Y-m-d 00:00:00');
            $todayEnd = date('Y-m-d 23:59:59');
            $playedCount = $this->memberGameLogModel
                ->where('member_id', $member['member_id'])
                ->where('game_id', $gameId)
                ->where('played_at >=', $todayStart)
                ->where('played_at <=', $todayEnd)
                ->countAllResults();

            if ($playedCount >= $dailyLimit) {
                return $this->response->setStatusCode(ResponseInterface::HTTP_TOO_MANY_REQUESTS)
                    ->setJSON(['message' => 'Daily limit reached']);
            }
        }

        $min = (int) ($game['points_min'] ?? 0);
        $max = (int) ($game['points_max'] ?? 0);
        $points = $max > $min ? random_int($min, $max) : $min;

        $now = date('Y-m-d H:i:s');
        $this->memberGameLogModel->insert([
            'member_id' => $member['member_id'],
            'game_id' => $gameId,
            'points_awarded' => $points,
            'result' => $result,
            'played_at' => $now,
            'metadata' => $metadata ? json_encode($metadata) : null,
        ]);

        if ($points > 0) {
            $this->memberPointModel->insert([
                'member_id' => $member['member_id'],
                'points' => $points,
                'type' => 'earn',
                'source' => 'game',
                'reference_id' => 'game:' . $gameId,
                'note' => 'Reward game',
                'created_at' => $now,
            ]);

            $this->memberModel->update($member['member_id'], [
                'points_balance' => (int) $member['points_balance'] + $points,
                'total_points_earned' => (int) $member['total_points_earned'] + $points,
                'updated_at' => $now,
            ]);

            $member['points_balance'] = (int) $member['points_balance'] + $points;
            $member['total_points_earned'] = (int) $member['total_points_earned'] + $points;
        }

        return $this->response->setJSON([
            'member_id' => $member['member_id'],
            'game_id' => $gameId,
            'points_awarded' => $points,
            'points_balance' => (int) $member['points_balance'],
        ]);
    }

    public function purchaseHistory(): ResponseInterface
    {
        $userId = $this->getAuthenticatedUserId() ?? (int) ($this->request->getGet('user_id') ?? $this->request->getPost('user_id'));
        if ($userId <= 0) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_BAD_REQUEST)
                ->setJSON(['message' => 'user_id is required']);
        }

        $orders = $this->orderModel
            ->where('user_id', $userId)
            ->orderBy('tanggal_pesan', 'DESC')
            ->findAll();

        $orderIds = array_map(static fn(array $order) => $order['order_id'], $orders);
        $itemStats = [];
        if (!empty($orderIds)) {
            $rows = $this->orderItemModel
                ->select('order_id, SUM(kuantitas) as total_items, SUM(kuantitas * harga_satuan) as items_total')
                ->whereIn('order_id', $orderIds)
                ->groupBy('order_id')
                ->findAll();

            foreach ($rows as $row) {
                $itemStats[$row['order_id']] = [
                    'total_items' => (int) ($row['total_items'] ?? 0),
                    'items_total' => (float) ($row['items_total'] ?? 0),
                ];
            }
        }

        $history = [];
        foreach ($orders as $order) {
            $stats = $itemStats[$order['order_id']] ?? ['total_items' => 0, 'items_total' => 0];
            $history[] = [
                'order_id' => $order['order_id'],
                'status' => $order['status_pesanan'],
                'total_harga' => (float) $order['total_harga'],
                'tanggal_pesan' => $order['tanggal_pesan'],
                'total_items' => $stats['total_items'],
                'items_total' => $stats['items_total'],
            ];
        }

        return $this->response->setJSON(['orders' => $history]);
    }

    protected function resolveMember(int $userId, int $memberId): ?array
    {
        if ($memberId > 0) {
            return $this->memberModel->find($memberId);
        }

        if ($userId <= 0) {
            return null;
        }

        $member = $this->memberModel->findByUserId($userId);
        if ($member) {
            return $member;
        }

        $memberCode = $this->generateMemberCode($userId);
        $now = date('Y-m-d H:i:s');

        $newId = $this->memberModel->insert([
            'user_id' => $userId,
            'member_code' => $memberCode,
            'tier' => 'regular',
            'points_balance' => 0,
            'total_points_earned' => 0,
            'total_points_redeemed' => 0,
            'status' => 1,
            'joined_at' => $now,
            'updated_at' => $now,
        ], true);

        return $this->memberModel->find($newId);
    }

    protected function generateMemberCode(int $userId): string
    {
        $seed = strtoupper(substr(md5($userId . microtime(true)), 0, 6));
        return 'MBR' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT) . $seed;
    }

    protected function getAuthenticatedUserId(): ?int
    {
        $header = $this->request->getHeaderLine('Authorization');
        if (!$header || stripos($header, 'Bearer ') !== 0) {
            return null;
        }

        $token = trim(substr($header, 7));
        if ($token === '') {
            return null;
        }

        $tokenHash = hash('sha256', $token);
        $now = date('Y-m-d H:i:s');

        $tokenRow = $this->apiTokenModel
            ->where('token_hash', $tokenHash)
            ->where('revoked_at', null)
            ->groupStart()
                ->where('expires_at', null)
                ->orWhere('expires_at >=', $now)
            ->groupEnd()
            ->first();

        if (!$tokenRow) {
            return null;
        }

        $this->apiTokenModel->update($tokenRow['id'], ['last_used_at' => $now]);

        return (int) $tokenRow['user_id'];
    }

    protected function getJsonPayload(): array
    {
        $json = $this->request->getJSON(true);
        if (is_array($json)) {
            return $json;
        }

        return $this->request->getPost() ?? [];
    }
}
