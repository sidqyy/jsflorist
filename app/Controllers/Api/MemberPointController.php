<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MemberModel;
use App\Models\MemberPointModel;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\HTTP\ResponseInterface;

class MemberPointController extends BaseController
{
    protected MemberModel $memberModel;
    protected MemberPointModel $memberPointModel;

    public function __construct()
    {
        $this->memberModel = new MemberModel();
        $this->memberPointModel = new MemberPointModel();
    }

    public function index(): ResponseInterface
    {
        $memberId = (int) ($this->request->getGet('member_id') ?? 0);
        if ($memberId <= 0) {
            return $this->respondError('member_id wajib diisi', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $member = $this->memberModel->find($memberId);
        if (!$member) {
            return $this->respondError('Member tidak ditemukan', ResponseInterface::HTTP_NOT_FOUND);
        }

        if (isset($member['status']) && (int) $member['status'] !== 1) {
            return $this->respondError('Member tidak aktif', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $limit = (int) ($this->request->getGet('limit') ?? 100);
        $limit = max(1, min($limit, 200));

        $history = $this->memberPointModel
            ->where('member_id', $memberId)
            ->orderBy('created_at', 'DESC')
            ->findAll($limit);

        return $this->respondSuccess('Saldo dan riwayat poin', [
            'points_balance' => (int) ($member['points_balance'] ?? 0),
            'history' => $history,
        ]);
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
