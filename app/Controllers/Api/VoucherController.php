<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\VoucherModel;
use CodeIgniter\HTTP\ResponseInterface;

class VoucherController extends BaseController
{
    public function applyVoucher(): ResponseInterface
    {
        $payload = $this->getJsonPayload();

        $voucherCode = strtoupper(trim((string) ($payload['voucher_code'] ?? '')));
        $site = trim((string) ($payload['site'] ?? 'poppyflorist'));

        if ($voucherCode === '') {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Kode voucher wajib diisi.'
            ]);
        }

        if (!in_array($site, ['all', 'jsflorist', 'poppyflorist'], true)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Website voucher tidak valid.'
            ]);
        }

        $cartItems = $payload['cart_items'] ?? [];

        if (is_string($cartItems)) {
            $cartItems = json_decode($cartItems, true) ?? [];
        }

        if (empty($cartItems) || !is_array($cartItems)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Data keranjang kosong atau tidak valid.'
            ]);
        }

        $voucherModel = new VoucherModel();

        $voucher = $voucherModel
            ->where('code', $voucherCode)
            ->first();

        if (!$voucher) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Kode voucher tidak ditemukan.'
            ]);
        }

        $voucherSite = $voucher['site'] ?? 'all';

        if ($voucherSite !== 'all' && $voucherSite !== $site) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Voucher tidak berlaku untuk website ini.'
            ]);
        }

        if ((int)($voucher['is_active'] ?? 0) !== 1) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Voucher sudah tidak aktif.'
            ]);
        }

        if (!empty($voucher['expires_at']) && strtotime($voucher['expires_at']) < time()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Voucher sudah kadaluarsa.'
            ]);
        }

        if (!empty($voucher['usage_limit']) && (int)($voucher['used_count'] ?? 0) >= (int)$voucher['usage_limit']) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Voucher sudah mencapai batas penggunaan.'
            ]);
        }

        $subtotal = 0;

        foreach ($cartItems as $item) {
            $price = (float)($item['price'] ?? 0);
            $qty = (int)($item['quantity'] ?? 1);

            if ($qty < 1) {
                $qty = 1;
            }

            $subtotal += $price * $qty;
        }

        if ($subtotal <= 0) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Subtotal keranjang tidak valid.'
            ]);
        }

        if (!empty($voucher['min_amount']) && $subtotal < (float)$voucher['min_amount']) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Minimal pembelian untuk voucher ini adalah Rp' . number_format((float)$voucher['min_amount'], 0, ',', '.')
            ]);
        }

        if (!empty($voucher['max_amount']) && $subtotal > (float)$voucher['max_amount']) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Maksimal pembelian untuk voucher ini adalah Rp' . number_format((float)$voucher['max_amount'], 0, ',', '.')
            ]);
        }

        $discountType = $voucher['discount_type'] ?? 'fixed';
        $discountValue = (float)($voucher['discount_value'] ?? 0);
        $discountAmount = 0;
        $freeShipping = 0;

        if ($discountType === 'percent') {
            $discountAmount = $subtotal * ($discountValue / 100);
        } elseif ($discountType === 'fixed') {
            $discountAmount = $discountValue;
        } elseif ($discountType === 'free_shipping') {
            $discountAmount = 0;
            $freeShipping = 1;
        }

        if ($discountAmount > $subtotal) {
            $discountAmount = $subtotal;
        }

        $totalAfterDiscount = max(0, $subtotal - $discountAmount);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Voucher berhasil diterapkan.',
            'voucher' => [
                'id' => $voucher['id'],
                'code' => $voucher['code'],
                'name' => $voucher['name'],
                'site' => $voucherSite,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'free_shipping' => $freeShipping,
                'points_cost' => (int)($voucher['points_cost'] ?? 0),
            ],
            'subtotal' => $subtotal,
            'discount_amount' => $discountAmount,
            'total_after_discount' => $totalAfterDiscount
        ]);
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