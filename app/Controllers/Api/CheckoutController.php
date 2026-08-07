<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\DiscountRuleModel;
use App\Models\FreeShippingRuleModel;
use App\Models\MemberModel;
use App\Models\MemberPointModel;
use App\Models\MemberVoucherModel;
use App\Models\OrderItemModel;
use App\Models\OrderModel;
use App\Models\ProductModel;
use App\Models\VoucherModel;
use App\Models\UserModel;
use App\Models\ApiTokenModel;
use App\Transformers\CheckoutOrderResource;
use CodeIgniter\HTTP\ResponseInterface;
use Config\Database;

class CheckoutController extends BaseController
{
    protected OrderModel $orderModel;
    protected OrderItemModel $orderItemModel;
    protected UserModel $userModel;
    protected ProductModel $productModel;
    protected VoucherModel $voucherModel;
    protected CategoryModel $categoryModel;
    protected DiscountRuleModel $discountRuleModel;
    protected FreeShippingRuleModel $freeShippingRuleModel;
    protected ApiTokenModel $apiTokenModel;
    protected MemberModel $memberModel;
    protected MemberPointModel $memberPointModel;
    protected MemberVoucherModel $memberVoucherModel;
    protected $session;
    protected $db;

    private array $storeLocations = [
        'Banjarbaru'  => ['lat' => -3.4398799, 'lon' => 114.8332947],
        'Banjarmasin' => ['lat' => -3.316694,  'lon' => 114.590111],
    ];
    private string $primaryStore = 'Banjarbaru';

    private float $geocodeToleranceMeters = 500;
    private string $bankTransferInstructions = 'Transfer sesuai nominal lalu unggah bukti pembayaran.';
    private array $bankTransferAccounts = [
        [
            'bank_name'      => 'Bank Negara Indonesia (BNI)',
            'account_number' => '0469677820',
            'account_holder' => 'Dannys Siburian',
            'branch'         => null,
        ],
    ];
    private array $qrisSettings = [
        'image_path'   => 'assets/img/qris.png',
        'instructions' => 'Scan kode QR menggunakan aplikasi pembayaran Anda lalu unggah bukti pembayaran.',
    ];

    public function __construct()
    {
        helper(['url']);
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->userModel = new UserModel();
        $this->productModel = new ProductModel();
        $this->voucherModel = new VoucherModel();
        $this->categoryModel = new CategoryModel();
        $this->discountRuleModel = new DiscountRuleModel();
        $this->freeShippingRuleModel = new FreeShippingRuleModel();
        $this->apiTokenModel = new ApiTokenModel();
        $this->memberModel = new MemberModel();
        $this->memberPointModel = new MemberPointModel();
        $this->memberVoucherModel = new MemberVoucherModel();
        $this->session = session();
        $this->db = Database::connect();
    }

    /**
     * Helper untuk memvalidasi apakah tanggal pengantaran adalah hari ini
     */
    private function isHariIni(?string $tanggalInput): bool
    {
        if (empty($tanggalInput)) {
            return false;
        }

        try {
            $cleanDateStr = str_replace('T', ' ', $tanggalInput);
            $tanggalPesanan = date('Y-m-d', strtotime($cleanDateStr));
            $timezone = new \DateTimeZone($this->appTimezone ?? 'Asia/Makassar');
            $hariIni = (new \DateTime('now', $timezone))->format('Y-m-d');

            return $tanggalPesanan === $hariIni;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function config(): ResponseInterface
    {
        $pickupLocationName = 'Banjarbaru';
        $pickupLocations = [$this->primaryStore];

        return $this->response->setJSON([
            'store' => [
                'name'        => $this->storeData['name'] ?? 'JS Florist',
                'phone'       => $this->storeData['phone'] ?? '+62823-5741-8002',
                'pickup_hint' => $pickupLocationName,
            ],
            'pickup_locations' => $pickupLocations,
            'discount_rules'   => $this->discountRuleModel->getActiveDiscounts(),
            'free_shipping_rules' => $this->freeShippingRuleModel->getActiveRules(),
            'delivery_limits_km'  => 25,
        ]);
    }

    public function estimateShipping(): ResponseInterface
    {
        $payload = $this->getJsonPayload();
        log_message('error', 'PAYLOAD ESTIMATESHIPPING: ' . json_encode($payload));
        
        $referer = $this->request->getServer('HTTP_REFERER');
        if (strpos($referer, 'poppyflorist.com') !== false) {
            $this->primaryStore = 'Banjarmasin';
        } else {
            $this->primaryStore = 'Banjarbaru';
        }

        $customerLat = (float) ($payload['to_lat'] ?? 0);
        $customerLon = (float) ($payload['to_lon'] ?? 0);
        $cartItems   = $payload['cart_items'] ?? [];
        $subtotal    = (float) ($payload['subtotal'] ?? 0);
        $tanggalPengantaran = $payload['tanggal_pengantaran'] ?? null;

        if (empty($cartItems)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Keranjang kosong.',
            ]);
        }

        $reference = $this->storeLocations[$this->primaryStore];
        
        if (empty($customerLat) || empty($customerLon)) {
            $coordsUsed = ['lat' => $reference['lat'], 'lon' => $reference['lon']];
            $rev = null;
            $shortestDistance = 0;
        } else {
            $coordsUsed = ['lat' => $customerLat, 'lon' => $customerLon];
            $rev = $this->getReverseGeocode($coordsUsed['lat'], $coordsUsed['lon']);
            $shortestDistance = $this->calculateHaversineDistance($reference['lat'], $reference['lon'], $coordsUsed['lat'], $coordsUsed['lon']);
        }
        $nearestStore = $this->primaryStore;

        $pricedItems = [];
        $totalProduk = 0.0;
        $productIdsInCart = []; 

        foreach ($cartItems as $item) {
            $pricing = $this->computeItemPricing($item, $tanggalPengantaran);
            
            $item['original_price'] = $pricing['original_price'];
            $item['price'] = $pricing['final_price'];
            $item['has_discount'] = $pricing['has_discount'];
            
            $pricedItems[] = $item;
            $totalProduk += $item['price'] * ((int)($item['quantity'] ?? $item['qty'] ?? 1));
            
            $currentCartProductId = trim($item['id'] ?? $item['product_id'] ?? $item['productId'] ?? '');
            if ($currentCartProductId !== '') {
                $productIdsInCart[] = (string)$currentCartProductId;
            }
        }

        $discounts = $this->calculateDiscounts($totalProduk, $pricedItems, $tanggalPengantaran);
        log_message('error', 'DISCOUNTS RESULT ESTIMATESHIPPING: ' . json_encode($discounts));
        $subtotalAfterDiscount = $totalProduk - $discounts['amount'];

        $freeRule = $this->freeShippingRuleModel->getApplicableRule($subtotalAfterDiscount, $shortestDistance);
        
        $isFreeShipping = false;
        if ($freeRule) {
            if ((int)$freeRule['apply_to_all'] === 1) {
                $isFreeShipping = true;
            } else {
                $allowedProductIds = array_map('trim', explode(',', (string)$freeRule['product_ids']));
                
                foreach ($productIdsInCart as $cartPid) {
                    if (in_array($cartPid, $allowedProductIds, true)) {
                        $isFreeShipping = true;
                        break; 
                    }
                }
            }
        }

        $shippingCost = $isFreeShipping ? 0 : $this->getShippingCostByDistance($shortestDistance, $pricedItems);

        return $this->response->setJSON([
            'status'            => 'success',
            'distance_km'       => round($shortestDistance, 2),
            'shipping_cost'     => $shippingCost,
            'nearest_store'     => $nearestStore,
            'discount_amount'   => $discounts['amount'],
            'subtotal_discount' => $discounts['subtotal_discount'],
            'product_discount'  => $discounts['product_discount'],
            'free_shipping'     => $isFreeShipping ? 1 : 0, 
            'geocode_mismatch'  => 0,
            'coords_used'       => $coordsUsed,
            'geocoded_display'  => $rev['display_name'] ?? null,
            'rev_address'       => $rev['address'] ?? null,
            'over_25km'         => $shortestDistance > 25,
        ]);
    }

    public function whatsappLink(): ResponseInterface
    {
        $payload = $this->getJsonPayload();

        $cartItems = $payload['cart_items'] ?? [];
        $customerData = $payload['customer'] ?? [];
        $tanggalPengantaran = $customerData['tanggal_pengantaran'] ?? null;

        if (empty($cartItems) || empty($customerData['nama']) || empty($customerData['nomor_hp'])) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Data pemesan atau keranjang belum lengkap.',
            ]);
        }

        // Hitung ulang penentuan harga untuk visual data di WhatsApp
        $pricedCartItems = [];
        foreach ($cartItems as $item) {
            $pricing = $this->computeItemPricing($item, $tanggalPengantaran);
            $item['price'] = $pricing['final_price'];
            $pricedCartItems[] = $item;
        }

        try {
            $message = $this->generateWhatsAppMessage($pricedCartItems, $customerData);
        } catch (\Throwable $e) {
            log_message('error', '[API WhatsApp] primary format failed: ' . $e->getMessage());
            $message = $this->generateSimpleWhatsAppMessage($pricedCartItems, $customerData);
        }

        $whatsappNumber = $this->getStoreWhatsAppNumber();

        return $this->response->setJSON([
            'status'       => 'success',
            'whatsapp_url' => "https://wa.me/{$whatsappNumber}?text={$message}",
        ]);
    }

    public function placeOrder(): ResponseInterface
    {
        $payload = $this->getJsonPayload();
        $referer = $this->request->getServer('HTTP_REFERER') ?? '';

        $currentStoreName = 'JS Florist';
        $currentSite = trim((string)($payload['site'] ?? ''));

        if ($currentSite === '') {
            $currentSite = strpos($referer, 'poppyflorist.com') !== false ? 'poppyflorist' : 'jsflorist';
        }

        if ($currentSite === 'poppyflorist') {
            $currentStoreName = 'Poppy Florist';
        }

        $cartItems = $payload['cart_items'] ?? [];

        if (empty($cartItems)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => 'Keranjang kosong.',
            ]);
        }

        $deliveryType = $payload['tipe_pengantaran'] ?? 'Delivery';
        $paymentMethod = $payload['metode_pembayaran'] ?? null;
        $tanggalPengantaran = $payload['tanggal_pengantaran'] ?? null;
        $authUserId = $this->getAuthenticatedUserId();
        $payloadUserId = (int) ($payload['user_id'] ?? 0);

        $validationError = $this->validateOrderPayload($payload, $deliveryType);

        if ($validationError !== null) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'error',
                'message' => $validationError,
            ]);
        }

        $pricedItems = [];
        $totalProduk = 0.0;

        foreach ($cartItems as $item) {
            $pricing = $this->computeItemPricing($item, $tanggalPengantaran);

            $item['original_price'] = $pricing['original_price'];
            $item['price'] = $pricing['final_price'];
            $item['has_discount'] = $pricing['has_discount'];

            $pricedItems[] = $item;
            $totalProduk += $item['price'] * ((int)($item['quantity'] ?? $item['qty'] ?? 1));
        }

        $discounts = $this->calculateDiscounts($totalProduk, $pricedItems, $tanggalPengantaran);
        $subtotalAfterDiscount = $totalProduk - $discounts['amount'];

        $memberVoucherId = (int) ($payload['member_voucher_id'] ?? 0);
        $memberVoucher = null;
        $memberVoucherDiscount = 0.0;
        $adminVoucher = null;
        $adminVoucherDiscount = 0.0;
        $voucherFreeShipping = false;

        // KONDISI: Cek voucher member
        if ($memberVoucherId > 0) {
            if (!$authUserId) {
                return $this->response->setStatusCode(401)->setJSON([
                    'status'  => 'error',
                    'message' => 'Login diperlukan untuk menggunakan voucher.',
                ]);
            }

            $member = $this->memberModel->findByUserId((int) $authUserId);

            if (!$member) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Member tidak ditemukan.',
                ]);
            }

            $now = date('Y-m-d H:i:s');

            $memberVoucher = $this->memberVoucherModel
                ->where('id', $memberVoucherId)
                ->where('member_id', $member['member_id'])
                ->where('status', 'active')
                ->groupStart()
                    ->where('expires_at', null)
                    ->orWhere('expires_at >=', $now)
                ->groupEnd()
                ->first();

            if (!$memberVoucher) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Voucher tidak valid atau sudah digunakan.',
                ]);
            }

            if (!empty($memberVoucher['min_amount']) && $subtotalAfterDiscount < (float) $memberVoucher['min_amount']) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Minimal pembelian belum memenuhi syarat voucher.',
                ]);
            }

            if (!empty($memberVoucher['max_amount']) && $subtotalAfterDiscount > (float) $memberVoucher['max_amount']) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Maksimal pembelian melebihi syarat voucher.',
                ]);
            }

            $voucherType = $memberVoucher['discount_type'] ?? 'percent';
            $voucherValue = (float) ($memberVoucher['discount_value'] ?? 0);

            if ($voucherType === 'percent') {
                $memberVoucherDiscount = round($subtotalAfterDiscount * ($voucherValue / 100), 2);
            } elseif ($voucherType === 'fixed') {
                $memberVoucherDiscount = min($voucherValue, $subtotalAfterDiscount);
            } elseif ($voucherType === 'free_shipping') {
                $voucherFreeShipping = true;
            }

            if ($memberVoucherDiscount > 0) {
                $subtotalAfterDiscount = max(0, $subtotalAfterDiscount - $memberVoucherDiscount);
            }
        }

        $voucherCode = strtoupper(trim((string)($payload['voucher_code'] ?? '')));

        // KONDISI: Cek voucher admin
        if ($voucherCode !== '') {
            $adminVoucher = $this->voucherModel
                ->where('code', $voucherCode)
                ->where('is_active', 1)
                ->first();

            if (!$adminVoucher) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Voucher tidak ditemukan atau tidak aktif.',
                ]);
            }

            $voucherSite = $adminVoucher['site'] ?? 'all';

            if ($voucherSite !== 'all' && $voucherSite !== $currentSite) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Voucher tidak berlaku untuk website ini.',
                ]);
            }

            if (!empty($adminVoucher['expires_at']) && strtotime($adminVoucher['expires_at']) < time()) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Voucher sudah kadaluarsa.',
                ]);
            }

            if (!empty($adminVoucher['usage_limit']) && (int)($adminVoucher['used_count'] ?? 0) >= (int)$adminVoucher['usage_limit']) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Voucher sudah mencapai batas penggunaan.',
                ]);
            }

            if (!empty($adminVoucher['min_amount']) && $subtotalAfterDiscount < (float)$adminVoucher['min_amount']) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Minimal pembelian untuk voucher ini adalah Rp' . number_format((float)$adminVoucher['min_amount'], 0, ',', '.'),
                ]);
            }

            if (!empty($adminVoucher['max_amount']) && $subtotalAfterDiscount > (float)$adminVoucher['max_amount']) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Maksimal pembelian untuk voucher ini adalah Rp' . number_format((float)$adminVoucher['max_amount'], 0, ',', '.'),
                ]);
            }

            $adminVoucherType = $adminVoucher['discount_type'] ?? 'fixed';
            $adminVoucherValue = (float)($adminVoucher['discount_value'] ?? 0);

            if ($adminVoucherType === 'percent') {
                $adminVoucherDiscount = round($subtotalAfterDiscount * ($adminVoucherValue / 100), 2);
            } elseif ($adminVoucherType === 'fixed') {
                $adminVoucherDiscount = min($adminVoucherValue, $subtotalAfterDiscount);
            } elseif ($adminVoucherType === 'free_shipping') {
                $voucherFreeShipping = true;
            }

            if ($adminVoucherDiscount > 0) {
                $subtotalAfterDiscount = max(0, $subtotalAfterDiscount - $adminVoucherDiscount);
            }
        }

        $shippingCost = 0;
        $coordsUsed = null;
        $finalAddressText = null;

        if ($deliveryType === 'Delivery') {
            $coordsUsed = [
                'lat' => (float) ($payload['alamat_latitude'] ?? 0),
                'lon' => (float) ($payload['alamat_longitude'] ?? 0),
            ];

            $rev = $this->getReverseGeocode($coordsUsed['lat'], $coordsUsed['lon']);
            $baseAddress = $rev['display_name'] ?? null;
            $alamatDetail = trim((string) ($payload['alamat_detail'] ?? ''));

            if ($baseAddress) {
                $finalAddressText = $baseAddress . ' - ' . $alamatDetail;
            } else {
                $finalAddressText = $alamatDetail;
            }

            $storeKey = ($currentStoreName === 'Poppy Florist') ? 'Banjarmasin' : 'Banjarbaru';
            $reference = $this->storeLocations[$storeKey] ?? $this->storeLocations['Banjarbaru'];

            $shortestDistance = $this->calculateHaversineDistance(
                $reference['lat'],
                $reference['lon'],
                $coordsUsed['lat'],
                $coordsUsed['lon']
            );

            if ($shortestDistance > 25) {
                return $this->response->setStatusCode(400)->setJSON([
                    'status'  => 'error',
                    'message' => 'Lokasi pengiriman di atas 25km dari toko. Mohon hubungi kami via WhatsApp.',
                ]);
            }

            $freeRule = $this->freeShippingRuleModel->getApplicableRule($subtotalAfterDiscount, $shortestDistance);
            $shippingCost = $freeRule ? 0 : $this->getShippingCostByDistance($shortestDistance, $pricedItems);
        }

        if ($voucherFreeShipping) {
            $shippingCost = 0;
        }

        $totalVoucherDiscount = $memberVoucherDiscount + $adminVoucherDiscount;
        $totalKeseluruhan = $subtotalAfterDiscount + $shippingCost;

        $nomorPemesan = $payload['nomor_pemesan'] ?? '';

        if (!empty($nomorPemesan)) {
            $oldOrders = $this->orderModel
                ->where('nomor_pemesan', $nomorPemesan)
                ->where('status_pesanan', 'Pending')
                ->findAll();

            if (!empty($oldOrders)) {
                foreach ($oldOrders as $oldOrder) {
                    $this->orderItemModel->where('order_id', $oldOrder['order_id'])->delete();
                    $this->orderModel->delete($oldOrder['order_id']);
                }
            }
        }

        $this->db->transBegin();

        try {
            $orderId = $this->orderModel->generateOrderId();

            $now = new \DateTime('now', new \DateTimeZone($this->appTimezone ?? 'Asia/Makassar'));
            $now->add(new \DateInterval('PT5M'));

            $orderData = [
                'order_id'               => $orderId,
                'tanggal_pesan'          => date('Y-m-d H:i:s'),
                'user_id'                => $authUserId ?: ($payloadUserId > 0 ? $payloadUserId : ($this->session->get('user_id') ?? null)),
                'total_harga'            => $totalKeseluruhan,
                'store_name'             => $currentStoreName,
                'metode_pembayaran'      => $paymentMethod,
                'tanggal_pengantaran'    => $payload['tanggal_pengantaran'] ?? null,
                'tipe_pengantaran'       => $deliveryType,
                'catatan_penerima'       => $payload['catatan_penerima'] ?? null,
                'penerima_nama'          => trim(($payload['nama_depan'] ?? '') . ' ' . ($payload['nama_belakang'] ?? '')),
                'penerima_nomor_hp'      => $deliveryType === 'Delivery' ? ($payload['penerima_nomor_hp'] ?? null) : null,
                'alamat_pengiriman_teks' => $deliveryType === 'Delivery' ? $finalAddressText : null,
                'alamat_latitude'        => $deliveryType === 'Delivery' ? $coordsUsed['lat'] : null,
                'alamat_longitude'       => $deliveryType === 'Delivery' ? $coordsUsed['lon'] : null,
                'nomor_pemesan'          => $payload['nomor_pemesan'] ?? null,
                'status_pesanan'         => 'Menunggu Bukti Transfer',
                'batas_waktu_pembayaran' => $now->format('Y-m-d H:i:s'),
                'diskon'                 => $discounts['amount'],
                'voucher_code'           => $voucherCode ?: null,
                'voucher_discount'       => $totalVoucherDiscount,
                'biaya_pengiriman'       => $shippingCost,
            ];

            $orderInsertResult = $this->orderModel->insert($orderData);

            if ($orderInsertResult === false) {
                $errors = $this->orderModel->errors();
                throw new \RuntimeException('Insert order failed: ' . json_encode($errors, JSON_UNESCAPED_UNICODE));
            }

            if (!empty($orderData['tanggal_pengantaran'])) {
                $cleanDeliveryStr = str_replace('T', ' ', $orderData['tanggal_pengantaran']);
                $justDateOnly = date('Y-m-d', strtotime($cleanDeliveryStr));

                $activeBonusRules = $this->db->table('bonus_rules')
                    ->where('is_active', 1)
                    ->where('DATE(start_date) <=', $justDateOnly)
                    ->where('DATE(end_date) >=', $justDateOnly)
                    ->where('quota_limit > usage_count')
                    ->get()
                    ->getResultArray();

                foreach ($activeBonusRules as $rule) {
                    $applicableProductIds = array_map('trim', explode(',', $rule['applicable_product_ids'] ?? ''));
                    $totalBonusPcsForThisRule = 0;
                    $isRuleMatched = false;

                    foreach ($pricedItems as $item) {
                        $currentCartProductId = trim($item['id'] ?? $item['product_id'] ?? $item['productId'] ?? '');

                        if ($currentCartProductId !== '' && in_array($currentCartProductId, $applicableProductIds, true)) {
                            $isRuleMatched = true;
                            $itemPrice = (int)($item['price'] ?? 0);
                            $tierConfig = json_decode($rule['bonus_config'] ?? '[]', true) ?? [];
                            krsort($tierConfig);

                            foreach ($tierConfig as $minPrice => $bonusAmount) {
                                if ($itemPrice >= (int)$minPrice) {
                                    $totalBonusPcsForThisRule += ((int)$bonusAmount * (int)($item['quantity'] ?? $item['qty'] ?? 1));
                                    break;
                                }
                            }
                        }
                    }

                    if ($isRuleMatched && $totalBonusPcsForThisRule > 0) {
                        $this->orderItemModel->insert([
                            'order_id'       => $orderId,
                            'product_id'     => $rule['bonus_id'],
                            'kuantitas'      => $totalBonusPcsForThisRule,
                            'harga_satuan'   => 0,
                            'custom_details' => json_encode(['note' => 'Hadiah Bonus Promo: ' . $rule['bonus_item_name']]),
                        ]);

                        $this->db->table('bonus_rules')
                            ->where('bonus_id', $rule['bonus_id'])
                            ->increment('usage_count', 1);
                    }
                }
            }

            foreach ($pricedItems as $item) {
                $textCustomNote = $item['options']['custom_details'] ?? $item['customDetails'] ?? $item['notes'] ?? null;

                if (is_array($textCustomNote)) {
                    $textCustomNote = json_encode($textCustomNote, JSON_UNESCAPED_UNICODE);
                }

                $this->orderItemModel->insert([
                    'order_id'       => $orderId,
                    'product_id'     => $item['id'] ?? $item['product_id'] ?? $item['productId'],
                    'kuantitas'      => $item['quantity'] ?? $item['qty'] ?? 1,
                    'harga_satuan'   => $item['price'],
                    'custom_details' => $textCustomNote,
                ]);
            }

            if ($memberVoucher) {
                $this->memberVoucherModel->update($memberVoucher['id'], [
                    'status' => 'used',
                    'used_at' => date('Y-m-d H:i:s'),
                    'order_id' => $orderId,
                ]);
            }

            if ($adminVoucher) {
                $this->voucherModel->update($adminVoucher['id'], [
                    'used_count' => ((int)($adminVoucher['used_count'] ?? 0)) + 1,
                ]);
            }

            $this->incrementDiscountUsage($discounts);
            $this->db->transCommit();

            // --- Mulai Integrasi Fonnte ---
            try {
                $fonnte = new \App\Libraries\Fonnte();
                $pesanCustomer = "Halo " . $orderData['penerima_nama'] . ",\n\nTerima kasih atas pesanan Anda di JS Florist via Aplikasi.\nOrder ID: *" . $orderId . "*\nTotal Pembayaran: *Rp" . number_format($totalKeseluruhan, 0, ',', '.') . "*\nStatus: Menunggu Pembayaran.\n\nHarap segera lakukan pembayaran agar pesanan dapat diproses.";
                
                if (!empty($orderData['nomor_pemesan'])) {
                    $fonnte->sendMessage($orderData['nomor_pemesan'], $pesanCustomer);
                }
            } catch (\Exception $e) {
                log_message('error', 'Fonnte Error API Checkout: ' . $e->getMessage());
            }
            // --- Akhir Integrasi Fonnte ---

            $order = $this->orderModel->find($orderId);

            return $this->response->setJSON($this->formatCheckoutOrderResponse($order));
        } catch (\Throwable $e) {
            $this->db->transRollback();

            log_message('error', '[API Checkout] Failed to place order: ' . $e->getMessage());

            return $this->response->setStatusCode(500)->setJSON([
                'status'  => 'error',
                'message' => 'Terjadi kesalahan saat memproses pesanan.',
            ]);
        }
    }

    public function showOrder(string $orderId): ResponseInterface
    {
        $order = $this->orderModel->find($orderId);

        if (!$order || !$this->isCheckoutOrder($order)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'failed',
                'message' => 'Pesanan tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON($this->formatCheckoutOrderResponse($order));
    }

    public function orderHistory(): ResponseInterface
    {
        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            return $this->response->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
                ->setJSON(['message' => 'Unauthorized']);
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

    public function uploadProof(): ResponseInterface
    {
        $orderId = $this->request->getPost('order_id');
        $file = $this->request->getFile('bukti_transfer');

        if (empty($orderId) || !$file) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'failed',
                'message' => 'Order ID dan file wajib diisi.',
            ]);
        }

        $order = $this->orderModel->find($orderId);
        if (!$order || !$this->isCheckoutOrder($order)) {
            return $this->response->setStatusCode(404)->setJSON([
                'status'  => 'failed',
                'message' => 'Pesanan tidak ditemukan.',
            ]);
        }

        $rules = [
            'bukti_transfer' => [
                'label' => 'Bukti Transfer',
                'rules' => 'uploaded[bukti_transfer]|max_size[bukti_transfer,2048]|ext_in[bukti_transfer,jpg,jpeg,png,pdf]',
                'errors' => [
                    'uploaded' => 'Harap upload file bukti transfer.',
                    'max_size' => 'Ukuran file bukti transfer maksimal 2MB.',
                    'ext_in'   => 'Format file hanya JPG, JPEG, PNG, atau PDF.',
                ],
            ],
        ];

        if (!$this->validate($rules)) {
            return $this->response->setStatusCode(422)->setJSON([
                'status' => 'failed',
                'errors' => $this->validator->getErrors(),
            ]);
        }

        if (!$file->isValid()) {
            return $this->response->setStatusCode(400)->setJSON([
                'status'  => 'failed',
                'message' => 'File tidak valid.',
            ]);
        }

        $uploadPath = ROOTPATH . 'public/uploads/proofs/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $newName = $orderId . '_' . $file->getRandomName();
        $file->move($uploadPath, $newName);

        $this->orderModel->update($orderId, [
            'status_pesanan' => 'Menunggu Verifikasi Admin',
            'bukti_bayar'    => 'uploads/proofs/' . $newName,
        ]);

        return $this->response->setJSON([
            'status'      => 'success',
            'message'     => 'Bukti pembayaran berhasil diupload.',
            'proof_path'  => 'uploads/proofs/' . $newName,
            'order_id'    => $orderId,
        ]);
    }

    protected function validateOrderPayload(array $payload, string $deliveryType): ?string
    {
        $required = ['nama_depan', 'nomor_pemesan', 'tanggal_pengantaran', 'metode_pembayaran'];
        foreach ($required as $field) {
            if (empty($payload[$field])) {
                return "Field {$field} wajib diisi.";
            }
        }

        if (!in_array($deliveryType, ['Delivery', 'Self-Pickup'], true)) {
            return 'Tipe pengantaran tidak valid.';
        }

        if ($deliveryType === 'Delivery') {
            if (empty($payload['penerima_nomor_hp']) || empty($payload['alamat_latitude']) || empty($payload['alamat_longitude']) || empty($payload['alamat_detail'])) {
                return 'Alamat pengiriman dan nomor penerima wajib diisi untuk delivery.';
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

    protected function generateMemberCode(int $userId): string
    {
        $seed = strtoupper(substr(md5($userId . microtime(true)), 0, 6));
        return 'MBR' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT) . $seed;
    }

    private function formatCheckoutOrderResponse(array $order, string $status = 'success'): array
    {
        $context = [
            'status' => $status,
            'bank'   => [
                'instructions'     => $this->bankTransferInstructions,
                'upload_proof_url' => site_url('payment/upload-proof'),
                'accounts'         => $this->bankTransferAccounts,
            ],
            'qris'   => [
                'image_url'        => base_url($this->qrisSettings['image_path']),
                'instructions'     => $this->qrisSettings['instructions'],
                'upload_proof_url' => site_url('payment/upload-proof'),
            ],
        ];

        return CheckoutOrderResource::make($order, $context);
    }

    private function isCheckoutOrder(array $order): bool
    {
        if (empty($order['metode_pembayaran'])) {
            return false;
        }

        return in_array($order['metode_pembayaran'], ['Direct Bank Transfer', 'QRIS'], true);
    }

    private function calculateHaversineDistance($lat1, $lon1, $lat2, $lon2)
    {
        $R = 6371; 
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $R * $c;
    }

    private function getReverseGeocode(float $lat, float $lon): ?array
    {
        $cacheKey = 'rev:' . $lat . ':' . $lon;
        $cached = $this->geocodeCacheGet($cacheKey);
        if ($cached !== null) {
            return $cached;
        }

        $url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' . urlencode($lat) . '&lon=' . urlencode($lon) . '&addressdetails=1';
        $opts = [
            'http' => [
                'method'  => 'GET',
                'header'  => "User-Agent: jsflorist/1.0 (+https://jsflorist.com)\r\nAccept-Language: en\r\n",
                'timeout' => 5,
            ],
        ];
        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);
        if ($result === false) {
            return null;
        }

        $data = json_decode($result, true);
        if (json_last_error() !== JSON_ERROR_NONE || empty($data)) {
            return null;
        }

        $payload = [
            'lat'          => (float) ($data['lat'] ?? $lat),
            'lon'          => (float) ($data['lon'] ?? $lon),
            'display_name' => $data['display_name'] ?? null,
            'address'      => $data['address'] ?? null,
        ];

        $this->geocodeCacheSet($cacheKey, $payload);
        return $payload;
    }

    private function geocodeCacheGet(string $key)
    {
        $dir = WRITEPATH . 'cache/geocode/';
        $file = $dir . md5($key) . '.json';
        if (!file_exists($file)) {
            return null;
        }
        $data = @file_get_contents($file);
        if ($data === false) {
            return null;
        }
        $obj = json_decode($data, true);
        if (!$obj) {
            return null;
        }
        if (isset($obj['__expires']) && time() > $obj['__expires']) {
            @unlink($file);
            return null;
        }
        return $obj['value'] ?? null;
    }

    private function geocodeCacheSet(string $key, $value, int $ttl = 2592000)
    {
        $dir = WRITEPATH . 'cache/geocode/';
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        $file = $dir . md5($key) . '.json';
        $obj = ['__expires' => time() + $ttl, 'value' => $value];
        @file_put_contents($file, json_encode($obj));
    }

    private function getShippingCostByDistance($distanceKm, array $cartItems)
    {
        $cost = 0;
        $hasBungaPapan = false;

        $bungaPapanCategoryNames = ['Wedding'];
        $bungaPapanCategories = $this->categoryModel->whereIn('nama_kategori', $bungaPapanCategoryNames)->findAll();
        $bungaPapanCategoryIds = array_column($bungaPapanCategories, 'category_id');

        if (!empty($bungaPapanCategoryIds)) {
            foreach ($cartItems as $item) {
                $cartId = $item['id'] ?? $item['product_id'] ?? $item['productId'] ?? null;
                if ($cartId) {
                    $productDetails = $this->productModel->find($cartId);
                    if ($productDetails && in_array($productDetails['category_id'], $bungaPapanCategoryIds)) {
                        $hasBungaPapan = true;
                        break;
                    }
                }
            }
        }

        if ($hasBungaPapan) {
            $cost = $distanceKm >= 16 ? 100000 : 0;
        } else {
            if ($distanceKm > 15) {
                $cost = 100000;
            } elseif ($distanceKm >= 11) {
                $cost = 50000;
            } elseif ($distanceKm >= 6) {
                $cost = 30000;
            } elseif ($distanceKm >= 1) {
                $cost = 20000;
            } else {
                $cost = 0;
            }
        }

        return $cost;
    }

    private function calculateDiscounts($subtotal, $cartItems = [], ?string $tanggalPengantaran = null)
    {
        $discountRule = $this->discountRuleModel->getApplicableDiscount($subtotal, $tanggalPengantaran);
        $discountPercentage = ($discountRule && isset($discountRule['discount_percentage'])) ? (float) $discountRule['discount_percentage'] : 0.0;
        $subtotalDiscountAmount = $subtotal * ($discountPercentage / 100.0);

        $productDiscountAmount = 0.0;
        $productDiscountRules = [];
        
        if (!empty($cartItems)) {
            foreach ($cartItems as $item) {
                $productId = $item['id'] ?? $item['product_id'] ?? $item['productId'] ?? null;
                if (!$productId) continue;

                $itemQty = (int)($item['quantity'] ?? $item['qty'] ?? 1);
                $itemOriginal = (float)($item['original_price'] ?? $item['price'] ?? 0);

                $productDiscount = $this->discountRuleModel->getProductDiscount($productId, $itemOriginal, $tanggalPengantaran);
                
                if ($productDiscount && isset($productDiscount['discount_amount'])) {
                    $productDiscountAmount += (float)$productDiscount['discount_amount'] * $itemQty;

                    $ruleId = $productDiscount['discount_id'];
                    if (!isset($productDiscountRules[$ruleId])) {
                        $productDiscountRules[$ruleId] = $productDiscount;
                    }
                }
            }
        }

        $totalDiscountAmount = $subtotalDiscountAmount + $productDiscountAmount;

        return [
            'amount'            => $totalDiscountAmount,
            'subtotal_discount' => $subtotalDiscountAmount,
            'product_discount'  => $productDiscountAmount,
            'percentage'        => $discountPercentage,
            'rule'              => $discountRule,
            'product_rules'     => array_values($productDiscountRules),
        ];
    }

    private function computeItemPricing(array $item, ?string $tanggalPengantaran = null): array
    {
        $productId = $item['id'] ?? $item['product_id'] ?? $item['productId'] ?? null;
        $quantity = (int)($item['quantity'] ?? $item['qty'] ?? 1);

        $originalPrice = (float)($item['original_price'] ?? $item['price'] ?? 0);
        $finalPrice = $originalPrice;
        $hasDiscount = false;
        $discountAmount = 0.0;
        $discountRule = null;

        // KONDISI: Cek diskon produk
        if ($productId) {
            $discountRule = $this->discountRuleModel->getProductDiscount($productId, $originalPrice, $tanggalPengantaran);
            if ($discountRule && !empty($discountRule['discounted_price']) && $discountRule['discounted_price'] > 0) {
                $finalPrice = (float)$discountRule['discounted_price'];
                if ($finalPrice < $originalPrice) {
                    $hasDiscount = true;
                    $discountAmount = ($originalPrice - $finalPrice) * $quantity;
                }
            }
        }

        return [
            'original_price' => $originalPrice,
            'final_price' => $finalPrice,
            'has_discount' => $hasDiscount,
            'discount_amount' => $discountAmount,
            'rule' => $discountRule,
        ];
    }

    public function checkBonusAjax(): ResponseInterface
    {
        $payload = $this->getJsonPayload();
        $tanggalPengantaran = $payload['tanggal_pengantaran'] ?? null;
        $cartItems = $payload['cart_items'] ?? [];

        if (empty($tanggalPengantaran) || empty($cartItems)) {
            return $this->response->setJSON(['status' => 'success', 'bonuses' => []]);
        }

        $cleanDateStr = str_replace('T', ' ', $tanggalPengantaran);
        $justDateOnly = date('Y-m-d', strtotime($cleanDateStr));
        
        $activeRules = $this->db->table('bonus_rules')
                               ->where('is_active', 1)
                               ->where('DATE(start_date) <=', $justDateOnly)
                               ->where('DATE(end_date) >=', $justDateOnly)
                               ->where('quota_limit > usage_count')
                               ->get()
                               ->getResultArray();

        $matchedBonuses = [];

        foreach ($activeRules as $rule) {
            $applicableProductIds = array_map('trim', explode(',', $rule['applicable_product_ids'] ?? ''));
            $totalBonusPcsForThisRule = 0;
            $isRuleMatched = false;

            foreach ($cartItems as $item) {
                $currentCartProductId = trim($item['id'] ?? $item['product_id'] ?? $item['productId'] ?? '');

                if ($currentCartProductId !== '' && in_array($currentCartProductId, $applicableProductIds, true)) {
                    $isRuleMatched = true;
                    
                    // Ganti perhitungan harga acuan menggunakan logic `computeItemPricing` berdasarkan tanggal
                    $pricing = $this->computeItemPricing($item, $tanggalPengantaran);
                    $itemPrice = (int)$pricing['final_price'];

                    $tierConfig = json_decode($rule['bonus_config'] ?? '[]', true) ?? [];
                    krsort($tierConfig);

                    foreach ($tierConfig as $minPrice => $bonusAmount) {
                        if ($itemPrice >= (int)$minPrice) {
                            $totalBonusPcsForThisRule += ((int)$bonusAmount * (int)($item['quantity'] ?? $item['qty'] ?? 1));
                            break;
                        }
                    }
                }
            }

            if ($isRuleMatched && $totalBonusPcsForThisRule > 0) {
                $matchedBonuses[] = [
                    'bonus_item_name' => $rule['bonus_item_name'],
                    'total_pcs'        => $totalBonusPcsForThisRule
                ];
            }
        }

        return $this->response->setJSON([
            'status' => 'success',
            'bonuses' => $matchedBonuses
        ]);
    }

    private function incrementDiscountUsage($discounts)
    {
        if (!empty($discounts['rule']['discount_id'])) {
            $this->discountRuleModel->incrementUsage($discounts['rule']['discount_id']);
        }

        if (!empty($discounts['product_rules'])) {
            foreach ($discounts['product_rules'] as $rule) {
                if (!empty($rule['discount_id'])) {
                    $this->discountRuleModel->incrementUsage($rule['discount_id']);
                }
            }
        }
    }
}