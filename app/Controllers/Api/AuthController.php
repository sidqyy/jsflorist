<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ApiTokenModel;
use App\Models\MemberModel;
use CodeIgniter\HTTP\ResponseInterface;

class AuthController extends BaseController
{
    protected ApiTokenModel $apiTokenModel;
    protected MemberModel $memberModel;

    public function __construct()
    {
        $this->apiTokenModel = new ApiTokenModel();
        $this->memberModel = new MemberModel();
    }

    public function me(): ResponseInterface
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->respondSuccess('OK', [
                'user_id' => null,
                'member_id' => null,
                'is_member' => false,
            ]);
        }

        $member = $this->memberModel->findByUserId($userId);
        $isMember = $member && (!isset($member['status']) || (int) $member['status'] === 1);

        return $this->respondSuccess('OK', [
            'user_id' => $userId,
            'member_id' => $isMember ? (int) $member['member_id'] : null,
            'is_member' => $isMember,
        ]);
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
}
