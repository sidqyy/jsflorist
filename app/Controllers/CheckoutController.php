<?php
namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\UserModel;
use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\DiscountRuleModel;
use App\Models\FreeShippingRuleModel;

use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

class CheckoutController extends BaseController
{
    protected $orderModel;
    protected $orderItemModel;
    protected $userModel;
    protected $productModel;
    protected $categoryModel;
    protected $discountRuleModel;
    protected $freeShippingRuleModel;
    protected $session;
    protected $request;
    protected $db;

    private $storeLocations = [
        'Banjarbaru' => ['lat' => -3.4398799, 'lon' => 114.8332947],
    ];

    private $geocodeToleranceMeters = 500;

    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        helper(['url', 'session']);

        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->userModel = new UserModel();
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->discountRuleModel = new DiscountRuleModel();
        $this->freeShippingRuleModel = new FreeShippingRuleModel();
        $this->session = session();
        $this->request = \Config\Services::request();
        $this->db = \Config\Database::connect();
    }

    /**
     * Helper untuk mengecek apakah tanggal yang dipilih customer adalah hari ini
     * Mendukung format YYYY-MM-DD maupun format lokal DD/MM/YYYY
     */
    private function isHariIni(?string $tanggalInput): bool
    {
        if (empty($tanggalInput)) {
            return false;
        }

        try {
            // Bersihkan karakter 'T' jika dikirim dari format datetime-local HTML5 standar
            $cleanDateStr = str_replace('T', ' ', $tanggalInput);
            
            // JIKA input menggunakan format lokal Indonesia (contoh: 17/07/2026)
            if (strpos($cleanDateStr, '/') !== false) {
                $parts = explode(' ', $cleanDateStr);
                $dateParts = explode('/', $parts[0]);
                
                if (count($dateParts) === 3) {
                    // Susun ulang menjadi format standar internasional YYYY-MM-DD
                    $tanggalPesanan = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
                } else {
                    $tanggalPesanan = date('Y-m-d', strtotime($cleanDateStr));
                }
            } else {
                // Jika formatnya sudah YYYY-MM-DD bawaan browser standar
                $tanggalPesanan = date('Y-m-d', strtotime($cleanDateStr));
            }
            
            // Ambil waktu hari ini berdasarkan timezone Asia/Makassar
            $timezone = new \DateTimeZone($this->appTimezone ?? 'Asia/Makassar');
            $hariIni = (new \DateTime('now', $timezone))->format('Y-m-d');

            return $tanggalPesanan === $hariIni;
        } catch (\Throwable $e) {
            return false;
        }
    }

    public function index()
    {
        $cartItems = $this->session->get('cart') ?? [];

        if (empty($cartItems)) {
            return redirect()->to('/cart')->with('error', 'Keranjang belanja Anda kosong, tidak dapat melanjutkan ke checkout.');
        }

        // Mengambil input tanggal pengantaran jika sudah ada dari request/session sebelumnya
        $tanggalPengantaran = $this->request->getGet('tanggal_pengantaran') ?? '';

        $pricedItems = [];
        $subtotalProduk = 0;
        $subtotalAwal = 0;

        foreach ($cartItems as $item) {
            $pricing = $this->computeItemPricing($item, $tanggalPengantaran);
            $item['original_price'] = $pricing['original_price'];
            $item['price'] = $pricing['final_price'];
            $item['has_discount'] = $pricing['has_discount'];
            $pricedItems[] = $item;
            
            $subtotalProduk += $item['price'] * $item['quantity'];
            $subtotalAwal += $item['original_price'] * $item['quantity'];
        }

        $data['cartItems'] = $pricedItems;
        $data['loggedInUser'] = null;

        if ($this->session->has('user_id')) {
            $userId = $this->session->get('user_id');
            $data['loggedInUser'] = $this->userModel->find($userId);
        }

        // Hitung diskon keseluruhan/subtotal berdasarkan tanggal saat ini
        $discountData = $this->calculateDiscounts($subtotalProduk, $pricedItems, $tanggalPengantaran);

        $data['subtotalAwal'] = $subtotalAwal;
        $data['subtotalProduk'] = $subtotalProduk;
        $data['discountData'] = $discountData; // Menyediakan data diskon ke view
        
        $data['appliedVoucher'] = $this->session->get('applied_voucher');
        
        $data['activeDiscounts'] = $this->discountRuleModel->getActiveDiscounts();
        $data['activeFreeShippingRules'] = $this->freeShippingRuleModel->getActiveRules();
        $data['store'] = $this->storeData;
        $data['pickupLocationName'] = 'Banjarbaru';

        return view('checkout', $data);
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

    private function geocodeAddress(string $address)
    {
        $address = trim($address);
        if ($address === '') return null;

        $url = 'https://nominatim.openstreetmap.org/search?format=json&limit=1&addressdetails=1&q=' . urlencode($address);

        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: jsflorist/1.0 (+https://jsflorist.com)\r\nAccept-Language: en\r\n",
                'timeout' => 5,
            ],
        ];

        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);

        if ($result === false) return null;

        $data = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($data)) return null;

        $row = $data[0];

        return [
            'lat' => (float)$row['lat'],
            'lon' => (float)$row['lon'],
            'display_name' => $row['display_name'] ?? '',
            'address' => $row['address'] ?? null,
        ];
    }

    private function reverseGeocode(float $lat, float $lon)
    {
        $url = 'https://nominatim.openstreetmap.org/reverse?format=json&lat=' . urlencode($lat) . '&lon=' . urlencode($lon) . '&addressdetails=1';

        $opts = [
            'http' => [
                'method' => 'GET',
                'header' => "User-Agent: jsflorist/1.0 (+https://yourdomain.example)\r\nAccept-Language: en\r\n",
                'timeout' => 5,
            ],
        ];

        $context = stream_context_create($opts);
        $result = @file_get_contents($url, false, $context);

        if ($result === false) return null;

        $data = json_decode($result, true);

        if (json_last_error() !== JSON_ERROR_NONE || empty($data)) return null;

        return [
            'lat' => (float)($data['lat'] ?? $lat),
            'lon' => (float)($data['lon'] ?? $lon),
            'display_name' => $data['display_name'] ?? null,
            'address' => $data['address'] ?? null,
        ];
    }

    private function geocodeCacheGet(string $key)
    {
        $dir = WRITEPATH . 'cache/geocode/';
        $file = $dir . md5($key) . '.json';

        if (!file_exists($file)) return null;

        $data = @file_get_contents($file);

        if ($data === false) return null;

        $obj = json_decode($data, true);

        if (!$obj) return null;

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

        @mkdir(dirname($file), 0755, true);
        @file_put_contents($file, json_encode($obj));
    }

    private function normalizeString(?string $s)
    {
        if ($s === null) return '';

        $s = mb_strtolower($s);
        $s = preg_replace('/[^\p{L}\p{N} ]+/u', '', $s);
        $s = preg_replace('/\s+/', ' ', $s);

        return trim($s);
    }

    private function compareAdminFields(?array $a, ?array $b)
    {
        if (empty($a) || empty($b)) return false;

        if (!empty($a['postcode']) && !empty($b['postcode']) && $this->normalizeString($a['postcode']) === $this->normalizeString($b['postcode'])) {
            return true;
        }

        $fields = ['suburb', 'city_district', 'city', 'town', 'village', 'municipality', 'county', 'state'];
        $scores = [];

        foreach ($fields as $f) {
            if (!empty($a[$f]) && !empty($b[$f])) {
                $na = $this->normalizeString($a[$f]);
                $nb = $this->normalizeString($b[$f]);

                if ($na === '' || $nb === '') continue;

                similar_text($na, $nb, $perc);
                $scores[] = $perc;
            }
        }

        if (empty($scores)) return false;

        $avg = array_sum($scores) / count($scores);

        return $avg >= 55.0;
    }

    private function metersBetween($lat1, $lon1, $lat2, $lon2)
    {
        return $this->calculateHaversineDistance($lat1, $lon1, $lat2, $lon2) * 1000.0;
    }

    private function getShippingCostByDistance($distanceKm, $cartItems)
    {
        $cost = 0;
        $hasBungaPapan = false;

        $bungaPapanCategoryNames = ['Wedding'];
        $bungaPapanCategories = $this->categoryModel->whereIn('nama_kategori', $bungaPapanCategoryNames)->findAll();
        $bungaPapanCategoryIds = array_column($bungaPapanCategories, 'category_id');

        if (!empty($bungaPapanCategoryIds)) {
            foreach ($cartItems as $item) {
                $productDetails = $this->productModel->find($item['id']);

                if ($productDetails && in_array($productDetails['category_id'], $bungaPapanCategoryIds)) {
                    $hasBungaPapan = true;
                    break;
                }
            }
        }

        if ($hasBungaPapan) {
            if ($distanceKm >= 16) {
                $cost = 100000;
            } else {
                $cost = 0;
            }
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

    private function generateWhatsAppMessage($cartItems, $customerData = [])
    {
        if (empty($cartItems) || !is_array($cartItems)) {
            throw new \InvalidArgumentException('Cart items is empty or invalid');
        }

        $message = "*PESANAN BUNGA - JS FLORIST*\n\n";

        if (!empty($customerData['nama'])) {
            $message .= " *Nama Pemesan:* " . $customerData['nama'] . "\n";
        }

        if (!empty($customerData['nomor_hp'])) {
            $message .= "*No. HP:* " . $customerData['nomor_hp'] . "\n";
        }

        if (!empty($customerData['alamat'])) {
            $message .= "*Alamat:* " . $customerData['alamat'] . "\n";
        }

        if (!empty($customerData['tanggal_pengantaran'])) {
            try {
                $tanggal = date('d/m/Y H:i', strtotime($customerData['tanggal_pengantaran']));
                $message .= " *Tanggal Pengantaran:* " . $tanggal . "\n";
            } catch (\Exception $e) {
                $message .= " *Tanggal Pengantaran:* " . $customerData['tanggal_pengantaran'] . "\n";
            }
        }

        $message .= "\n*DETAIL PESANAN:*\n";
        $message .= "━━━━━━━━━━━━━━━━━━━━\n";

        $totalHarga = 0;
        $itemNumber = 1;

        foreach ($cartItems as $index => $item) {
            try {
                $price = 0;
                $quantity = 1;
                $name = 'Produk';

                if (isset($item['price'])) {
                    if (is_numeric($item['price'])) {
                        $price = (float)$item['price'];
                    } elseif (is_string($item['price'])) {
                        $cleanPrice = preg_replace('/[^0-9.]/', '', $item['price']);
                        if (is_numeric($cleanPrice)) {
                            $price = (float)$cleanPrice;
                        }
                    }
                }

                if (isset($item['quantity'])) {
                    if (is_numeric($item['quantity'])) {
                        $quantity = max(1, (int)$item['quantity']);
                    } elseif (is_string($item['quantity'])) {
                        $cleanQty = preg_replace('/[^0-9]/', '', $item['quantity']);
                        if (is_numeric($cleanQty) && $cleanQty > 0) {
                            $quantity = (int)$cleanQty;
                        }
                    }
                }

                if (isset($item['name']) && is_string($item['name']) && !empty($item['name'])) {
                    $name = $item['name'];
                } elseif (isset($item['nama_produk'])) {
                    $name = $item['nama_produk'];
                }

                $subtotal = (float)$price * (int)$quantity;
                $totalHarga += $subtotal;

                $message .= $itemNumber . ". *" . (string)$name . "*\n";
                $message .= "   Qty: " . (string)$quantity . " x Rp" . number_format((float)$price, 0, ',', '.') . "\n";

                if (isset($item['options']['custom_details']) && !empty($item['options']['custom_details'])) {
                    $message .= "   Catatan: " . (string)$item['options']['custom_details'] . "\n";
                }

                $message .= "   Subtotal: Rp" . number_format((float)$subtotal, 0, ',', '.') . "\n\n";

                $itemNumber++;
            } catch (\Exception $e) {
                log_message('error', '[WhatsApp Debug] Error processing cart item ' . $index . ': ' . $e->getMessage());
                $message .= $itemNumber . ". *Error loading product*\n\n";
                $itemNumber++;
            }
        }

        // Hitung potongan jika ada diskon subtotal / voucher
        $tanggalPengantaranInput = $customerData['tanggal_pengantaran'] ?? null;

        $message .= "━━━━━━━━━━━━━━━━━━━━\n";
        $message .= "*Total: Rp" . number_format((float)$totalHarga, 0, ',', '.') . "*\n\n";
        $message .= "*Lokasi pengiriman di atas 25km*\n";
        $message .= "Untuk ongkir mohon konfirmasi via WhatsApp\n\n";

        if (!empty($customerData['catatan'])) {
            $message .= "*Catatan:* " . (string)$customerData['catatan'] . "\n\n";
        }

        $message .= "Terika kasih! ";

        return urlencode($message);
    }

    private function generateSimpleWhatsAppMessage($cartItems, $customerData = [])
    {
        $message = "*PESANAN BUNGA - JS FLORIST*\n\n";

        if (!empty($customerData['nama'])) {
            $message .= "Nama: " . $customerData['nama'] . "\n";
        }

        if (!empty($customerData['nomor_hp'])) {
            $message .= "HP: " . $customerData['nomor_hp'] . "\n";
        }

        if (!empty($customerData['alamat'])) {
            $message .= "Alamat: " . $customerData['alamat'] . "\n";
        }

        if (!empty($customerData['tanggal_pengantaran'])) {
            $message .= "Tanggal: " . $customerData['tanggal_pengantaran'] . "\n";
        }

        $message .= "\nPESANAN:\n";

        $totalCount = 0;

        foreach ($cartItems as $index => $item) {
            try {
                $name = $item['name'] ?? 'Produk';
                $qty = isset($item['quantity']) && is_numeric($item['quantity']) ? (int)$item['quantity'] : 1;

                $message .= "- " . $name . " (Qty: " . $qty . ")\n";
                $totalCount += $qty;
            } catch (\Exception $e) {
                $message .= "- Produk (Error)\n";
            }
        }

        $message .= "\nTotal Item: " . $totalCount . "\n";
        $message .= "Lokasi >25km - Ongkir via WhatsApp\n\n";

        if (!empty($customerData['catatan'])) {
            $message .= "Catatan: " . $customerData['catatan'] . "\n\n";
        }

        $message .= "Terima kasih!";

        return urlencode($message);
    }

    private function getStoreWhatsAppNumber()
    {
        $phoneNumber = $this->storeData['phone'] ?? '+62823-5741-8002';
        $cleanNumber = preg_replace('/[^\d+]/', '', $phoneNumber);

        if (strpos($cleanNumber, '+62') === 0) {
            $whatsappNumber = substr($cleanNumber, 1);
        } elseif (strpos($cleanNumber, '62') === 0) {
            $whatsappNumber = $cleanNumber;
        } elseif (strpos($cleanNumber, '08') === 0) {
            $whatsappNumber = '62' . substr($cleanNumber, 1);
        } elseif (strpos($cleanNumber, '8') === 0) {
            $whatsappNumber = '62' . $cleanNumber;
        } else {
            $whatsappNumber = '6282357418002';
        }

        return $whatsappNumber;
    }

    public function generateWhatsAppUrl()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON([
                'status' => 'error',
                'message' => 'Method Not Allowed'
            ]);
        }

        try {
            $cartItems = $this->session->get('cart') ?? [];

            if (empty($cartItems)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Keranjang kosong'
                ]);
            }

            $namaDepan = trim($this->request->getPost('nama_depan') ?? '');
            $namaBelakang = trim($this->request->getPost('nama_belakang') ?? '');
            $nomorPemesan = trim($this->request->getPost('nomor_pemesan') ?? '');
            $alamatTeks = trim($this->request->getPost('alamat_pengiriman_teks') ?? '');
            $alamatDetail = trim($this->request->getPost('alamat_detail') ?? '');
            $tanggalPengantaran = trim($this->request->getPost('tanggal_pengantaran') ?? '');
            $catatan = trim($this->request->getPost('catatan_penerima') ?? '');

            if (empty($namaDepan) || empty($nomorPemesan)) {
                return $this->response->setJSON([
                    'status' => 'error',
                    'message' => 'Nama dan nomor pemesan harus diisi'
                ]);
            }

            // Hitung ulang item pricing sesuai tanggal untuk pesan WhatsApp
            $pricedCartItems = [];
            foreach ($cartItems as $item) {
                $pricing = $this->computeItemPricing($item, $tanggalPengantaran);
                $item['price'] = $pricing['final_price'];
                $pricedCartItems[] = $item;
            }

            $customerData = [
                'name' => $namaDepan . ($namaBelakang ? ' ' . $namaBelakang : ''),
                'nomor_hp' => $nomorPemesan,
                'alamat' => $alamatTeks . ($alamatDetail ? ' - ' . $alamatDetail : ''),
                'tanggal_pengantaran' => $tanggalPengantaran,
                'catatan' => $catatan
            ];

            try {
                $message = $this->generateWhatsAppMessage($pricedCartItems, $customerData);
            } catch (\Exception $e) {
                log_message('error', '[WhatsApp] Main method failed, using fallback: ' . $e->getMessage());
                $message = $this->generateSimpleWhatsAppMessage($pricedCartItems, $customerData);
            }

            $whatsappNumber = $this->getStoreWhatsAppNumber();
            $whatsappUrl = "https://wa.me/{$whatsappNumber}?text={$message}";

            return $this->response->setJSON([
                'status' => 'success',
                'whatsapp_url' => $whatsappUrl,
                'message' => 'URL WhatsApp berhasil dibuat'
            ]);
        } catch (\Exception $e) {
            log_message('error', '[WhatsApp URL Generation] Error: ' . $e->getMessage());

            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Terjadi kesalahan saat membuat URL WhatsApp: ' . $e->getMessage()
            ]);
        }
    }

    private function computeItemPricing(array $item, ?string $tanggalPengantaran = null): array
    {
        $productId = $item['id'] ?? null;
        $quantity = (int)($item['quantity'] ?? 1);

        // REVISI UTAMA: Ambil harga jual asli langsung dari database produk agar acuan hitung ulang AJAX valid (tidak bias harga diskon keranjang)
        $originalPrice = 0.0;
        if ($productId) {
            $dbProduct = $this->productModel->find($productId);
            if ($dbProduct) {
                $originalPrice = (float)($dbProduct['harga'] ?? $dbProduct['harga_jual'] ?? $item['price'] ?? 0);
            }
        }

        if ($originalPrice == 0) {
            $originalPrice = (float)($item['original_price'] ?? $item['price'] ?? 0);
        }

        $finalPrice = $originalPrice;
        $hasDiscount = false;
        $discountAmount = 0.0;
        $discountRule = null;

        // KONDISI: Cek apakah produk memiliki diskon sesuai database
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

    private function calculateDiscounts($subtotal, $cartItems = [], ?string $tanggalPengantaran = null)
    {
        // FIX: Lewatkan parameter $tanggalPengantaran agar model mencocokkan waktu input customer, bukan waktu server NOW()
        $discountRule = $this->discountRuleModel->getApplicableDiscount($subtotal, $tanggalPengantaran);
        $discountPercentage = 0.0;

        if ($discountRule && isset($discountRule['discount_percentage'])) {
            $discountPercentage = (float)$discountRule['discount_percentage'];
        }

        $subtotalDiscountAmount = 0.0;

        if ($discountPercentage > 0) {
            $subtotalDiscountAmount = $subtotal * ($discountPercentage / 100.0);
        }

        $productDiscountAmount = 0.0;
        $productDiscountRules = [];

        if (!empty($cartItems)) {
            foreach ($cartItems as $item) {
                $productId = $item['id'] ?? null;

                if (!$productId) continue;

                $itemQty = (int)($item['quantity'] ?? 1);
                $itemOriginal = (float)($item['original_price'] ?? $item['price'] ?? 0);
                $itemFinal = (float)($item['price'] ?? 0);

                if ($itemOriginal > $itemFinal) {
                    $itemDiscount = ($itemOriginal - $itemFinal) * $itemQty;
                    $productDiscountAmount += $itemDiscount;

                    $productDiscount = $this->discountRuleModel->getProductDiscount($productId, $itemOriginal, $tanggalPengantaran);

                    if ($productDiscount) {
                        $ruleId = $productDiscount['discount_id'];

                        if (!isset($productDiscountRules[$ruleId])) {
                            $productDiscountRules[$ruleId] = $productDiscount;
                        }
                    }
                }
            }
        }

        $totalDiscountAmount = $subtotalDiscountAmount + $productDiscountAmount;

        if ($totalDiscountAmount > $subtotal) {
            $totalDiscountAmount = $subtotal;
        }

        return [
            'amount' => $totalDiscountAmount,
            'subtotal_discount' => $subtotalDiscountAmount,
            'product_discount' => $productDiscountAmount,
            'percentage' => $discountPercentage,
            'rule' => $discountRule,
            'product_rules' => array_values($productDiscountRules),
        ];
    }

    public function estimateShipping()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(405)->setJSON([
                'status' => 'error',
                'message' => 'Method Not Allowed'
            ]);
        }

        $customerLat = (float)$this->request->getPost('to_lat');
        $customerLon = (float)$this->request->getPost('to_lon');
        $cartItemsJson = $this->request->getPost('cart_items_json');
        $tanggalPengantaran = $this->request->getPost('tanggal_pengantaran') ?? '';

        if (empty($cartItemsJson)) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Data keranjang tidak lengkap.'
            ]);
        }

        $cartItems = json_decode($cartItemsJson, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->response->setJSON([
                'status' => 'error',
                'message' => 'Format data keranjang tidak valid.'
            ]);
        }

        // 1. Hitung ulang harga item berdasarkan tanggal pengantaran menggunakan data mutlak database produk
        $pricedCartItems = [];
        $subtotalProdukCalculated = 0;
        $totalOriginalPriceCalculated = 0;

        foreach ($cartItems as $item) {
            $pricing = $this->computeItemPricing($item, $tanggalPengantaran);
            $item['original_price'] = $pricing['original_price'];
            $item['price'] = $pricing['final_price'];
            $pricedCartItems[] = $item;
            
            $subtotalProdukCalculated += $pricing['final_price'] * ($item['quantity'] ?? 1);
            $totalOriginalPriceCalculated += $pricing['original_price'] * ($item['quantity'] ?? 1);
        }

        $subtotalAwal = $totalOriginalPriceCalculated;

        $coordsUsed = [
            'lat' => $customerLat ?: $this->storeLocations['Banjarbaru']['lat'],
            'lon' => $customerLon ?: $this->storeLocations['Banjarbaru']['lon'],
        ];

        $cacheKeyRev = 'rev:' . $coordsUsed['lat'] . ':' . $coordsUsed['lon'];
        $rev = $this->geocodeCacheGet($cacheKeyRev);

        if ($rev === null && $customerLat && $customerLon) {
            $rev = $this->reverseGeocode($customerLat, $customerLon);
            if ($rev) {
                $this->geocodeCacheSet($cacheKeyRev, $rev);
            }
        }

        $distanceToBjb = $this->calculateHaversineDistance(
            $this->storeLocations['Banjarbaru']['lat'],
            $this->storeLocations['Banjarbaru']['lon'],
            $coordsUsed['lat'],
            $coordsUsed['lon']
        );

        $shortestDistance = $customerLat ? $distanceToBjb : 0;
        $nearestStore = 'Banjarbaru';
        
        $subtotalAfterDiscount = $subtotalProdukCalculated;
        $discountAmount = 0;

        $discountAmount = $subtotalAwal - $subtotalProdukCalculated;

        $discountRule = $this->discountRuleModel->getApplicableDiscount($subtotalProdukCalculated, $tanggalPengantaran);
        if ($discountRule && isset($discountRule['discount_percentage'])) {
            $discountPercentage = (float)$discountRule['discount_percentage'];
            $subtotalDiscountAmount = $subtotalProdukCalculated * ($discountPercentage / 100.0);
            $discountAmount += $subtotalDiscountAmount;
            $subtotalAfterDiscount -= $subtotalDiscountAmount;
        }

        $appliedVoucher = $this->session->get('applied_voucher');

        if ($appliedVoucher) {
            $voucherDiscount = (float)($appliedVoucher['discount_amount'] ?? 0);

            if ($voucherDiscount > $subtotalAfterDiscount) {
                $voucherDiscount = $subtotalAfterDiscount;
            }

            $discountAmount += $voucherDiscount;
            $subtotalAfterDiscount -= $voucherDiscount;
        }

        $shippingCost = 0;
        $freeShipping = 0;
        
        if ($customerLat && $customerLon) {
            $freeRule = $this->freeShippingRuleModel->getApplicableRule($subtotalAfterDiscount, $shortestDistance);

            if (
                $freeRule ||
                (!empty($appliedVoucher) && ($appliedVoucher['free_shipping'] ?? 0) == 1)
            ) {
                $shippingCost = 0;
                $freeShipping = 1;
            } else {
                $shippingCost = $this->getShippingCostByDistance($shortestDistance, $pricedCartItems);
                $freeShipping = 0;
            }
        }

        $isOverLimit = $customerLat ? ($shortestDistance > 25) : false;

        return $this->response->setJSON([
            'status' => 'success',
            'distance_km' => round($shortestDistance, 2),
            'shipping_cost' => $shippingCost,
            'nearest_store' => $nearestStore,
            'discount_amount' => $discountAmount,
            'free_shipping' => $freeShipping,
            'geocode_mismatch' => 0,
            'coords_used' => $coordsUsed,
            'geocoded_display' => $rev['display_name'] ?? null,
            'rev_address' => $rev['address'] ?? null,
            'over_25km' => $isOverLimit,
        ]);
    }

    public function process()
    {
        $tipePengantaran = $this->request->getPost('tipe_pengantaran');
        $tanggalPengantaranInput = $this->request->getPost('tanggal_pengantaran');

        $rules = [
            'nama_depan' => 'required|min_length[3]|max_length[100]',
            'nomor_pemesan' => 'required|max_length[20]',
            'tanggal_pengantaran' => 'required|valid_date[Y-m-d\TH:i]',
            'tipe_pengantaran' => 'required|in_list[Delivery,Self-Pickup]',
            'metode_pembayaran' => 'required|in_list[Direct Bank Transfer,QRIS]',
        ];

        if ($tipePengantaran === 'Delivery') {
            $rules['penerima_nomor_hp'] = 'required|max_length[20]';
            $rules['alamat_latitude'] = 'required|decimal';
            $rules['alamat_longitude'] = 'required|decimal';
            $rules['alamat_detail'] = 'required|max_length[255]';
        }

        if (!$this->validate($rules)) {
            return redirect()->to('/checkout')->withInput()->with('errors', $this->validator->getErrors());
        }

        $cartItems = $this->session->get('cart') ?? [];
        if (empty($cartItems)) {
            return redirect()->to('/cart')->with('error', 'Keranjang belanja Anda kosong.');
        }

        $pricedCartItems = [];
        $totalHargaProduk = 0;
        $subtotalAwal = 0;

        foreach ($cartItems as $item) {
            $pricing = $this->computeItemPricing($item, $tanggalPengantaranInput);
            $item['original_price'] = $pricing['original_price'];
            $item['price'] = $pricing['final_price'];
            $item['has_discount'] = $pricing['has_discount'];
            $pricedCartItems[] = $item;

            $totalHargaProduk += $pricing['final_price'] * ($item['quantity'] ?? 1);
            $subtotalAwal += $pricing['original_price'] * ($item['quantity'] ?? 1);
        }

        $subtotalAfterDiscount = $totalHargaProduk;
        $discountAmount = $subtotalAwal - $totalHargaProduk;

        $discountRule = $this->discountRuleModel->getApplicableDiscount($totalHargaProduk, $tanggalPengantaranInput);
        if ($discountRule && isset($discountRule['discount_percentage'])) {
            $discountPercentage = (float)$discountRule['discount_percentage'];
            $subtotalDiscountAmount = $totalHargaProduk * ($discountPercentage / 100.0);
            $discountAmount += $subtotalDiscountAmount;
            $subtotalAfterDiscount -= $subtotalDiscountAmount;
        }

        $biayaPengiriman = 0;

        $appliedVoucher = $this->session->get('applied_voucher');
        if ($appliedVoucher) {
            $voucherDiscount = (float)($appliedVoucher['discount_amount'] ?? 0);
            if ($voucherDiscount > $subtotalAfterDiscount) {
                $voucherDiscount = $subtotalAfterDiscount;
            }
            $discountAmount += $voucherDiscount;
            $subtotalAfterDiscount -= $voucherDiscount;
        }

        if ($tipePengantaran === 'Delivery') {
            $customerLat = (float)$this->request->getPost('alamat_latitude');
            $customerLon = (float)$this->request->getPost('alamat_longitude');
            $shortestDistance = $this->calculateHaversineDistance($this->storeLocations['Banjarbaru']['lat'], $this->storeLocations['Banjarbaru']['lon'], $customerLat, $customerLon);

            $freeRule = $this->freeShippingRuleModel->getApplicableRule($subtotalAfterDiscount, $shortestDistance);
            if ($freeRule || (!empty($appliedVoucher) && ($appliedVoucher['free_shipping'] ?? 0) == 1)) {
                $biayaPengiriman = 0;
            } else {
                $biayaPengiriman = $this->getShippingCostByDistance($shortestDistance, $pricedCartItems);
            }
        }

        $totalKeseluruhan = $subtotalAfterDiscount + $biayaPengiriman;
        $this->db->transBegin();

        try {
            $orderId = $this->orderModel->generateOrderId();
            $now = new \DateTime('now', new \DateTimeZone('Asia/Makassar'));
            $now->add(new \DateInterval('PT5M'));

            $orderData = [
                'order_id' => $orderId,
                'user_id' => $this->session->get('user_id') ?? null,
                'tanggal_pesan' => date('Y-m-d H:i:s'),
                'status_pesanan' => 'Menunggu Bukti Transfer',
                'total_harga' => $totalKeseluruhan,
                'store_name' => $this->storeData['name'] ?? 'JS Florist',
                'metode_pembayaran' => $this->request->getPost('metode_pembayaran'),
                'tanggal_pengantaran' => $this->request->getPost('tanggal_pengantaran'),
                'tipe_pengantaran' => $tipePengantaran,
                'catatan_penerima' => $this->request->getPost('catatan_penerima'),
                'penerima_nama' => trim($this->request->getPost('nama_depan') . ' ' . $this->request->getPost('nama_belakang')),
                'penerima_nomor_hp' => $tipePengantaran === 'Delivery' ? $this->request->getPost('penerima_nomor_hp') : null,
                'alamat_pengiriman_teks' => $tipePengantaran === 'Delivery' ? $this->request->getPost('alamat_pengiriman_teks') : null,
                'alamat_latitude' => $tipePengantaran === 'Delivery' ? $customerLat : null,
                'alamat_longitude' => $tipePengantaran === 'Delivery' ? $customerLon : null,
                'nomor_pemesan' => $this->request->getPost('nomor_pemesan'),
                'pickup_location' => $tipePengantaran === 'Self-Pickup' ? $this->request->getPost('pickup_location') : null,
                'batas_waktu_pembayaran' => $now->format('Y-m-d H:i:s'),
                'diskon' => $discountAmount,
                'biaya_pengiriman' => $biayaPengiriman,
            ];

            $this->orderModel->insert($orderData);

            foreach ($pricedCartItems as $item) {
                $this->orderItemModel->insert([
                    'order_id' => $orderId,
                    'product_id' => $item['id'],
                    'kuantitas' => $item['quantity'],
                    'harga_satuan' => $item['price'],
                    'custom_details' => $item['options']['custom_details'] ?? null,
                ]);
            }

            $this->db->transCommit();
            $this->session->remove('cart');
            $this->session->remove('applied_voucher');

            // --- Mulai Integrasi Fonnte ---
            try {
                $fonnte = new \App\Libraries\Fonnte();
                $pesanCustomer = "Halo " . $orderData['penerima_nama'] . ",\n\nTerima kasih atas pesanan Anda di JS Florist.\nOrder ID: *" . $orderId . "*\nTotal Pembayaran: *Rp" . number_format($totalKeseluruhan, 0, ',', '.') . "*\nStatus: Menunggu Pembayaran.\n\nHarap segera lakukan pembayaran agar pesanan dapat diproses.";
                
                if (!empty($orderData['nomor_pemesan'])) {
                    $fonnte->sendMessage($orderData['nomor_pemesan'], $pesanCustomer);
                }
            } catch (\Exception $e) {
                log_message('error', 'Fonnte Error: ' . $e->getMessage());
            }
            // --- Akhir Integrasi Fonnte ---

            if ($orderData['metode_pembayaran'] === 'QRIS') {
                return redirect()->to('/checkout/qris/' . $orderId);
            }
            return redirect()->to('/payment/bank-transfer/' . $orderId);
        } catch (\Exception $e) {
            $this->db->transRollback();
            return redirect()->to('/checkout')->withInput()->with('error', 'Gagal memproses pesanan: ' . $e->getMessage());
        }
    }

    public function orderSuccess($orderId)
    {
        $order = $this->orderModel->find($orderId);
        if (!$order) return redirect()->to('/dashboard')->with('error', 'Nomor pesanan tidak valid.');
        return view('order_success', ['order' => $order, 'store' => $this->storeData]);
    }

    public function checkBonusAjax()
    {
        $tanggalPengantaran = $this->request->getPost('tanggal_pengantaran');
        $cartItemsRaw = $this->request->getPost('cart_items') ?? '[]';
        $cartItems = json_decode($cartItemsRaw, true) ?? [];

        if (empty($tanggalPengantaran) || empty($cartItems)) {
            return $this->response->setJSON(['status' => 'success', 'bonuses' => []]);
        }

        $formattedDeliveryDate = date('Y-m-d H:i:s', strtotime($tanggalPengantaran));
        $activeRules = $this->db->table('bonus_rules')->where('is_active', 1)->where('start_date <=', $formattedDeliveryDate)->where('end_date >=', $formattedDeliveryDate)->where('quota_limit > usage_count')->get()->getResultArray();
        $matchedBonuses = [];

        foreach ($activeRules as $rule) {
            $applicableProductIds = array_map('trim', explode(',', $rule['applicable_product_ids'] ?? ''));
            $totalBonusPcsForThisRule = 0;
            $isRuleMatched = false;

            foreach ($cartItems as $item) {
                $currentCartProductId = trim($item['id'] ?? '');
                if ($currentCartProductId !== '' && in_array($currentCartProductId, $applicableProductIds, true)) {
                    $isRuleMatched = true;
                    $pricing = $this->computeItemPricing($item, $tanggalPengantaran);
                    $itemPrice = (int)$pricing['final_price'];
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
                $matchedBonuses[] = ['bonus_item_name' => $rule['bonus_item_name'], 'total_pcs' => $totalBonusPcsForThisRule];
            }
        }

        return $this->response->setJSON(['status' => 'success', 'bonuses' => $matchedBonuses]);
    }

    public function showQrisPage($orderId)
    {
        $order = $this->orderModel->find($orderId);
        if (!$order) return redirect()->to('/tracking');
        return view('payment_qris', ['order' => $order, 'store' => $this->storeData]);
    }
}