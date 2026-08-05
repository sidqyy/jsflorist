<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\MemberModel;
use App\Models\MemberPointModel;
use App\Models\MemberVoucherModel;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class MemberVoucherController extends BaseController
{
    protected MemberModel $memberModel;
    protected MemberPointModel $memberPointModel;
    protected MemberVoucherModel $memberVoucherModel;
    protected $db;

    public function __construct()
    {
        $this->memberModel = new MemberModel();
        $this->memberPointModel = new MemberPointModel();
        $this->memberVoucherModel = new MemberVoucherModel();
        $this->db = Database::connect();
    }

    public function redeem(): ResponseInterface
    {
        $payload = $this->getJsonPayload();

        $memberId = (int) ($payload['member_id'] ?? 0);
        $voucherName = trim((string) ($payload['voucher_name'] ?? ''));
        $discountType = trim((string) ($payload['discount_type'] ?? ''));
        $discountValue = $payload['discount_value'] ?? null;
        $minAmount = $payload['min_amount'] ?? null;
        $maxAmount = $payload['max_amount'] ?? null;
        $expiresAt = $payload['expires_at'] ?? null;
        $pointsCost = (int) ($payload['points_cost'] ?? 0);

        if ($memberId <= 0 || $voucherName === '' || $discountType === '') {
            return $this->respondError('member_id, voucher_name, dan discount_type wajib diisi', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $allowedTypes = ['percent', 'fixed', 'free_shipping'];
        if (!in_array($discountType, $allowedTypes, true)) {
            return $this->respondError('discount_type tidak valid', ResponseInterface::HTTP_BAD_REQUEST);
        }

        if ($discountType !== 'free_shipping') {
            if (!is_numeric($discountValue) || (float) $discountValue <= 0) {
                return $this->respondError('discount_value wajib diisi dan lebih dari 0', ResponseInterface::HTTP_BAD_REQUEST);
            }
        }

        if ($pointsCost <= 0) {
            return $this->respondError('points_cost wajib diisi', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $member = $this->memberModel->find($memberId);
        if (!$member) {
            return $this->respondError('Member tidak ditemukan', ResponseInterface::HTTP_NOT_FOUND);
        }

        if (isset($member['status']) && (int) $member['status'] !== 1) {
            return $this->respondError('Member tidak aktif', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $currentBalance = (int) ($member['points_balance'] ?? 0);
        if ($currentBalance < $pointsCost) {
            return $this->respondError('Poin tidak mencukupi', ResponseInterface::HTTP_BAD_REQUEST, [
                'saldo_poin' => $currentBalance,
                'points_cost' => $pointsCost,
            ]);
        }

        $expiry = $this->normalizeExpiry($expiresAt);
        if ($expiresAt !== null && $expiry === null) {
            return $this->respondError('Format expires_at tidak valid', ResponseInterface::HTTP_BAD_REQUEST);
        }

        $now = date('Y-m-d H:i:s');
        $voucherCode = $this->generateVoucherCode();

        $this->db->transBegin();
        try {
            $this->memberVoucherModel->insert([
                'member_id' => $memberId,
                'voucher_code' => $voucherCode,
                'voucher_name' => $voucherName,
                'discount_type' => $discountType,
                'discount_value' => $discountType === 'free_shipping' ? 0 : (float) $discountValue,
                'min_amount' => is_numeric($minAmount) ? (float) $minAmount : null,
                'max_amount' => is_numeric($maxAmount) ? (float) $maxAmount : null,
                'expires_at' => $expiry,
                'status' => 'active',
                'created_at' => $now,
                'used_at' => null,
                'order_id' => null,
            ]);

            $this->memberPointModel->insert([
                'member_id' => $memberId,
                'points' => $pointsCost,
                'type' => 'redeem',
                'source' => 'voucher',
                'reference_id' => 'voucher:' . $voucherCode,
                'note' => 'Redeem voucher',
                'created_at' => $now,
            ]);

            $newBalance = $currentBalance - $pointsCost;
            $newTotalRedeemed = (int) ($member['total_points_redeemed'] ?? 0) + $pointsCost;

            $this->memberModel->update($memberId, [
                'points_balance' => $newBalance,
                'total_points_redeemed' => $newTotalRedeemed,
                'updated_at' => $now,
            ]);

            $this->db->transCommit();
        } catch (\Throwable $e) {
            $this->db->transRollback();
            return $this->respondError('Gagal menukar voucher', ResponseInterface::HTTP_INTERNAL_SERVER_ERROR);
        }

        return $this->respondSuccess('Voucher berhasil ditukar', [
            'voucher_code' => $voucherCode,
            'saldo_poin_baru' => $newBalance,
        ]);
    }

    protected function generateVoucherCode(): string
    {
        $prefix = 'MV-' . date('ymd');
        for ($i = 0; $i < 5; $i++) {
            $code = $prefix . '-' . strtoupper(bin2hex(random_bytes(3)));
            $exists = $this->memberVoucherModel->where('voucher_code', $code)->first();
            if (!$exists) {
                return $code;
            }
        }

        return $prefix . '-' . strtoupper(bin2hex(random_bytes(4)));
    }

    protected function normalizeExpiry($expiresAt): ?string
    {
        if ($expiresAt === null || $expiresAt === '') {
            return null;
        }

        $expiresAt = trim((string) $expiresAt);
        $formats = ['Y-m-d H:i:s', 'Y-m-d'];
        foreach ($formats as $format) {
            $date = \DateTime::createFromFormat($format, $expiresAt);
            $errors = \DateTime::getLastErrors();
            if ($date && ($errors['warning_count'] ?? 0) === 0 && ($errors['error_count'] ?? 0) === 0) {
                return $format === 'Y-m-d' ? $date->format('Y-m-d 23:59:59') : $date->format('Y-m-d H:i:s');
            }
        }

        return null;
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
