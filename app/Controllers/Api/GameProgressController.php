<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ApiTokenModel;
use App\Models\MemberModel;
use App\Models\MemberGameProgressModel;
use CodeIgniter\HTTP\ResponseInterface;

class GameProgressController extends BaseController
{
    public const MEMBERSHIP_GATE_LEVEL = 6;

    protected ApiTokenModel $apiTokenModel;
    protected MemberModel $memberModel;
    protected MemberGameProgressModel $progressModel;

    public function __construct()
    {
        $this->apiTokenModel = new ApiTokenModel();
        $this->memberModel = new MemberModel();
        $this->progressModel = new MemberGameProgressModel();
    }

    public function show(): ResponseInterface
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->respondError('Unauthorized', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $member = $this->memberModel->findByUserId($userId);
        $isMember = $member && (!isset($member['status']) || (int) $member['status'] === 1);

        $progress = $this->findProgress($userId, $isMember ? (int) $member['member_id'] : null);
        if (!$progress) {
            return $this->respondSuccess('OK', [
                'current_level' => 1,
                'max_unlocked_level' => 1,
                'updated_at' => null,
            ]);
        }

        return $this->respondSuccess('OK', [
            'current_level' => (int) $progress['current_level'],
            'max_unlocked_level' => (int) $progress['max_unlocked_level'],
            'updated_at' => $progress['updated_at'],
        ]);
    }

    public function store(): ResponseInterface
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->respondError('Unauthorized', ResponseInterface::HTTP_UNAUTHORIZED);
        }

        $payload = $this->getJsonPayload();
        $currentLevel = (int) ($payload['current_level'] ?? 0);
        $maxUnlocked = (int) ($payload['max_unlocked_level'] ?? 0);

        if ($currentLevel < 1 || $maxUnlocked < 1) {
            return $this->respondError('Level harus >= 1', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $member = $this->memberModel->findByUserId($userId);
        $isMember = $member && (!isset($member['status']) || (int) $member['status'] === 1);

        if (!$isMember && $currentLevel >= self::MEMBERSHIP_GATE_LEVEL) {
            return $this->respondError('Level ini hanya untuk member', ResponseInterface::HTTP_FORBIDDEN);
        }

        $memberId = $isMember ? (int) $member['member_id'] : null;
        $existing = $this->findProgress($userId, $memberId);

        $data = [
            'user_id' => $userId,
            'member_id' => $memberId,
            'current_level' => $currentLevel,
            'max_unlocked_level' => $maxUnlocked,
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($existing) {
            $this->progressModel->update($existing['id'], $data);
        } else {
            $this->progressModel->insert($data);
        }

        return $this->respondSuccess('Progress tersimpan');
    }

    protected function findProgress(int $userId, ?int $memberId): ?array
    {
        if ($memberId) {
            $row = $this->progressModel->where('member_id', $memberId)->first();
            if ($row) {
                return $row;
            }
        }

        return $this->progressModel->where('user_id', $userId)->first();
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

    protected function respondSuccess(string $message, array $data = [], int $statusCode = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON([
                'status' => 'success',
                'message' => $message,
                'data' => $data,
            ]);
    }

    protected function respondError(string $message, int $statusCode = 400, ?array $data = null): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON([
                'status' => 'error',
                'message' => $message,
                'data' => $data,
            ]);
    }
}
