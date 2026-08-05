<?php
namespace App\Controllers;

use App\Models\ProductModel;
use App\Models\VoucherModel;

class CartController extends BaseController
{
    public function __construct()
    {
        helper(['url', 'session']);
    }

    public function add()
    {
        $session = session();
        $request = \Config\Services::request();
        $productModel = new ProductModel();

        $productId = $request->getPost('product_id');
        $quantity = $request->getPost('quantity') ?? 1;

        if (empty($productId) || trim($productId) === '') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'ID Produk tidak valid (kosong).'
            ]);
        }

        if (!is_numeric($quantity) || (int)$quantity < 1) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kuantitas tidak valid.'
            ]);
        }

        $quantity = (int)$quantity;
        $product = $productModel->find($productId);

        if (!$product) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan.'
            ]);
        }

        $cart = $session->get('cart') ?? [];

        $formPrice = $request->getPost('product_price');
        $itemPrice = isset($formPrice) ? (float)$formPrice : (float)$product['harga'];

        $customDetails = $request->getPost('custom_details') ?? [];
        $variantName = is_array($customDetails) ? ($customDetails['variant_name'] ?? null) : null;

        $cartItemId = $productId;

        if ($variantName) {
            $cartItemId = $productId . '-' . crc32($variantName);
        }

        if ($productId === 'PRDKUANG') {
            $pecahan = (int)($customDetails['pecahan'] ?? 0);
            $nominal = (int)($customDetails['nominal'] ?? 0);
            $lembar = ($pecahan > 0) ? floor($nominal / $pecahan) : 0;
            $upahJasa = $this->calculateUpahBuketUang($lembar);

            $biayaPenukaran = (int)($customDetails['biaya_penukaran'] ?? 0);

            if (($customDetails['money_source_type'] ?? '') === 'uang_dari_toko') {
                $itemPrice = $nominal + $upahJasa + $biayaPenukaran;
            } elseif (($customDetails['money_source_type'] ?? '') === 'uang_sendiri') {
                $itemPrice = $upahJasa;
            }

            $quantity = 1;
            $customDetails['upah'] = $upahJasa;
            $customDetails['lembar'] = $lembar;

            $cartItemId = 'PRDKUANG-' . $itemPrice;
        }

        if (isset($cart[$cartItemId])) {
            $cart[$cartItemId]['quantity'] += $quantity;
        } else {
            $originalPrice = $request->getPost('original_price') ?? $product['harga'];
            $formPrice = $request->getPost('product_price');

            $itemPrice = (float)$formPrice;

            if ($itemPrice <= 0) {
                $itemPrice = (float)$originalPrice;
            }

            $hasDiscount = $request->getPost('has_discount') === '1';

            $cart[$cartItemId] = [
                'id'             => $product['product_id'],
                'product_id'     => $product['product_id'],
                'cart_id'        => $cartItemId,
                'name'           => $product['nama_produk'],
                'price'          => $itemPrice,
                'original_price' => (float)$originalPrice,
                'has_discount'   => $hasDiscount,
                'quantity'       => $quantity,
                'image'          => $product['gambar_url'],
                'options'        => [
                    'custom_details' => !empty($customDetails) ? json_encode($customDetails) : null,
                ]
            ];
        }

        $session->set('cart', $cart);
        $session->remove('applied_voucher');

        $totalItemsInCart = 0;

        foreach ($cart as $item) {
            $totalItemsInCart += $item['quantity'];
        }

        return $this->response->setJSON([
            'status' => 'success',
            'message' => $product['nama_produk'] . ' berhasil ditambahkan ke keranjang!',
            'cart_total_items' => $totalItemsInCart
        ]);
    }

    private function calculateUpahBuketUang(int $lembar): int
    {
        if ($lembar >= 5 && $lembar <= 20) {
            return 250000;
        } elseif ($lembar >= 21 && $lembar <= 40) {
            return 400000;
        } elseif ($lembar >= 41 && $lembar <= 60) {
            return 600000;
        } elseif ($lembar >= 61 && $lembar <= 80) {
            return 800000;
        } elseif ($lembar >= 81 && $lembar <= 100) {
            return 1000000;
        }

        return 0;
    }

    public function index()
    {
        $session = session();

        $data['cartItems'] = $session->get('cart') ?? [];
        $data['appliedVoucher'] = $session->get('applied_voucher');
        $data['store'] = $this->storeData;

        return view('cart', $data);
    }

    public function applyVoucher()
    {
        $session = session();
        $request = \Config\Services::request();
        $voucherModel = new VoucherModel();

        $cart = $session->get('cart') ?? [];

        if (empty($cart)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Keranjang masih kosong.'
            ]);
        }

        $voucherCode = strtoupper(trim((string)$request->getPost('voucher_code')));

        if ($voucherCode === '') {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kode voucher wajib diisi.'
            ]);
        }

        $voucher = $voucherModel->where('code', $voucherCode)->first();

        if (!$voucher) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kode voucher tidak ditemukan.'
            ]);
        }

        if ((int)($voucher['is_active'] ?? 0) !== 1) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Voucher sudah tidak aktif.'
            ]);
        }

        if (!empty($voucher['expires_at']) && strtotime($voucher['expires_at']) < time()) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Voucher sudah kadaluarsa.'
            ]);
        }

        $subtotal = 0;

        foreach ($cart as $item) {
            $subtotal += ((float)$item['price'] * (int)$item['quantity']);
        }

        if (!empty($voucher['min_amount']) && $subtotal < (float)$voucher['min_amount']) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Minimal pembelian untuk voucher ini adalah Rp' . number_format((float)$voucher['min_amount'], 0, ',', '.')
            ]);
        }

        if (!empty($voucher['max_amount']) && $subtotal > (float)$voucher['max_amount']) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Maksimal pembelian untuk voucher ini adalah Rp' . number_format((float)$voucher['max_amount'], 0, ',', '.')
            ]);
        }

        if (!empty($voucher['usage_limit']) && (int)$voucher['used_count'] >= (int)$voucher['usage_limit']) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Voucher sudah mencapai batas penggunaan.'
            ]);
        }

        $pointsCost = (int)($voucher['points_cost'] ?? 0);

        $discountType = $voucher['discount_type'];
        $discountValue = (float)$voucher['discount_value'];
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

        $session->set('applied_voucher', [
            'voucher_id' => $voucher['id'],
            'code' => $voucher['code'],
            'name' => $voucher['name'],
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'discount_amount' => $discountAmount,
            'points_cost' => $pointsCost,
            'free_shipping' => $freeShipping,
        ]);

        return $this->response->setJSON([
            'status' => 'success',
            'message' => 'Voucher berhasil diterapkan.',
            'discount_amount' => $discountAmount,
            'free_shipping' => $freeShipping
        ]);
    }

    public function remove($productId)
    {
        $session = session();
        $cart = $session->get('cart') ?? [];

        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            $session->set('cart', $cart);
            $session->remove('applied_voucher');

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Produk berhasil dihapus dari keranjang.'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Produk tidak ditemukan di keranjang.'
        ]);
    }

    public function update()
    {
        $session = session();
        $request = \Config\Services::request();

        $productId = $request->getPost('product_id');
        $quantity = $request->getPost('quantity');

        $cart = $session->get('cart') ?? [];
        $quantity = (int)$quantity;

        if ($quantity < 1) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Kuantitas tidak valid.'
            ]);
        }

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] = $quantity;
            $session->set('cart', $cart);
            $session->remove('applied_voucher');

            return $this->response->setJSON([
                'status' => 'success',
                'message' => 'Kuantitas produk berhasil diperbarui.'
            ]);
        }

        return $this->response->setJSON([
            'status' => 'error',
            'message' => 'Produk tidak ditemukan di keranjang.'
        ]);
    }

    public function calculateBonusByDate($tanggalInput)
    {
        if (empty($tanggalInput)) {
            return [];
        }

        $formattedDate = date('Y-m-d H:i:s', strtotime($tanggalInput));
        $db = \Config\Database::connect();

        $activeRules = $db->table('bonus_rules')
            ->where('is_active', 1)
            ->where('start_date <=', $formattedDate)
            ->where('end_date >=', $formattedDate)
            ->where('quota_limit > usage_count')
            ->get()
            ->getResultArray();

        $cartItems = session()->get('cart') ?? [];
        $matchedBonuses = [];

        foreach ($activeRules as $rule) {
            $applicableProductIds = array_map('trim', explode(',', $rule['applicable_product_ids'] ?? ''));
            $totalBonusPcsForThisRule = 0;
            $isRuleMatched = false;

            foreach ($cartItems as $item) {
                $currentCartProductId = trim($item['id'] ?? $item['product_id'] ?? '');

                if ($currentCartProductId !== '' && in_array($currentCartProductId, $applicableProductIds, true)) {
                    $isRuleMatched = true;
                    $itemPrice = (int)$item['price'];
                    $tierConfig = json_decode($rule['bonus_config'] ?? '[]', true) ?? [];
                    krsort($tierConfig);

                    foreach ($tierConfig as $minPrice => $bonusAmount) {
                        if ($itemPrice >= (int)$minPrice) {
                            $totalBonusPcsForThisRule += ((int)$bonusAmount * (int)($item['quantity'] ?? 1));
                            break;
                        }
                    }
                }
            }

            if ($isRuleMatched && $totalBonusPcsForThisRule > 0) {
                $matchedBonuses[] = [
                    'bonus_id'        => $rule['bonus_id'],
                    'bonus_item_name' => $rule['bonus_item_name'],
                    'total_pcs'       => $totalBonusPcsForThisRule
                ];
            }
        }

        return $matchedBonuses;
    }
}