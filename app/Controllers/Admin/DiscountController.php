<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\DiscountRuleModel;
use App\Models\ProductModel;

class DiscountController extends BaseController
{
    protected $discountModel;
    protected $productModel;

    public function __construct()
    {
        $this->discountModel = new DiscountRuleModel();
        $this->productModel = new ProductModel();
    }

    /**
     * Halaman daftar aturan diskon
     */
    public function index()
    {
        $data['discounts'] = $this->discountModel->orderBy('discount_type', 'ASC')->orderBy('min_amount', 'ASC')->findAll();
        $data['store'] = $this->storeData;
        
        // Ambil semua produk untuk referensi nama
        $products = $this->productModel->findAll();
        $data['products_map'] = [];
        foreach ($products as $product) {
            $data['products_map'][$product['product_id']] = $product['nama_produk'];
        }
        
        return view('admin/discount/index', $data);
    }

    /**
     * Halaman form tambah aturan diskon
     */
    public function create()
    {
        $data['store'] = $this->storeData;
        $data['products'] = $this->productModel->where('is_active', 1)->orderBy('nama_produk', 'ASC')->findAll();
        return view('admin/discount/create', $data);
    }

    /**
     * Debug endpoint untuk test store - HAPUS SETELAH DEBUG
     */
    public function testStore()
    {
        return $this->response->setJSON([
            'status' => 'ok',
            'message' => 'Store endpoint is reachable',
            'post_data' => $this->request->getPost(),
            'session' => [
                'isLoggedIn' => session()->get('isLoggedIn'),
                'role' => session()->get('role')
            ]
        ]);
    }

    /**
     * Proses simpan aturan diskon baru
     */
    public function store()
    {
        log_message('info', '[DiscountController::store] === START STORE DISCOUNT ===');
        
        try {
            // Log semua POST data untuk debugging
            $allPostData = $this->request->getPost();
            log_message('info', '[DiscountController::store] POST Data: ' . json_encode($allPostData));
            
            // Normalize inputs
            $name = trim($this->request->getPost('name') ?? '');
            $discountType = $this->request->getPost('discount_type') ?? 'subtotal';
            $productIds = $this->request->getPost('product_ids') ?? [];
            $productPrices = $this->request->getPost('product_prices') ?? []; 
            $rawMin = $this->request->getPost('min_amount');
            $rawMax = $this->request->getPost('max_amount');
            $rawPct = $this->request->getPost('discount_percentage');
            $isActive = $this->request->getPost('is_active');
            $usageLimit = $this->request->getPost('usage_limit');
            $startDate = $this->request->getPost('start_date');
            $endDate = $this->request->getPost('end_date');
            $validPickupStartTime = $this->request->getPost('valid_pickup_start_time');
            $validPickupEndTime = $this->request->getPost('valid_pickup_end_time');
            
            $min = $this->normalizeNumber($rawMin);
            $max = ($rawMax === '' || $rawMax === null) ? null : $this->normalizeNumber($rawMax);
            $pct = $this->normalizeNumber($rawPct);
            $usageLimit = ($usageLimit === '' || $usageLimit === null) ? null : (int)$usageLimit;

            // Validasi berdasarkan tipe
            if ($discountType === 'product') {
                if (empty($productIds)) {
                    return redirect()->back()->withInput()->with('error', 'Pilih minimal 1 produk untuk diskon produk.');
                }
                
                foreach ($productIds as $productId) {
                    if (empty($productPrices[$productId]) || (float)$productPrices[$productId] <= 0) {
                        return redirect()->back()->withInput()->with('error', 'Harga diskon harus diisi untuk semua produk yang dipilih.');
                    }
                }
                
                $min = 0; $max = 0; $pct = 0;
            } else {
                if (empty($min) || $min <= 0) {
                    return redirect()->back()->withInput()->with('errors', ['min_amount' => 'Minimal pembelian harus diisi untuk diskon subtotal.']);
                }
            }

            // Validasi untuk tipe subtotal
            if ($discountType === 'subtotal') {
                $validation = \Config\Services::validation();
                $validation->setRules([
                    'discount_percentage' => 'required|numeric|greater_than[0]|less_than_equal_to[100]',
                    'is_active' => 'required|in_list[0,1]'
                ]);

                if (!$validation->run(['discount_percentage' => $pct, 'is_active' => $isActive])) {
                    return redirect()->back()->withInput()->with('errors', $validation->getErrors());
                }
            }

            if ($discountType === 'subtotal' && $max !== null && $max <= $min) {
                return redirect()->back()->withInput()->with('errors', ['max_amount' => 'Maksimal pembelian harus lebih besar dari minimal pembelian']);
            }

            if (!empty($startDate) && !empty($endDate) && $startDate > $endDate) {
                return redirect()->back()->withInput()->with('error', 'Tanggal mulai harus sebelum tanggal berakhir.');
            }

            // Build product_ids JSON
            $productIdsJson = null;
            if ($discountType === 'product') {
                $productData = [];
                foreach ($productIds as $productId) {
                    $productData[$productId] = ['discounted_price' => (float)$productPrices[$productId]];
                }
                $productIdsJson = json_encode($productData);
            }

            $data = [
                'name' => $name ?: null,
                'discount_type' => $discountType,
                'product_ids' => $productIdsJson,
                'min_amount' => $min ?? 0,
                'max_amount' => $max ?? 0,
                'discount_percentage' => $pct ?? 0,
                'is_active' => (int) $isActive,
                'usage_limit' => $usageLimit,
                'usage_count' => 0,
                'start_date' => $startDate ?: null,
                'end_date' => $endDate ?: null,
                'valid_pickup_start_time' => $validPickupStartTime ?: null,
                'valid_pickup_end_time' => $validPickupEndTime ?: null,
            ];

            if ($discountType === 'subtotal' && !$this->validateDiscountRange($data['min_amount'], $data['max_amount'])) {
                return redirect()->back()->withInput()->with('error', 'Range pembelian bertabrakan dengan aturan diskon yang sudah ada.');
            }

            if ($this->discountModel->insert($data)) {
                return redirect()->to(base_url('admin/discounts'))->with('success', 'Aturan diskon berhasil ditambahkan.');
            } else {
                return redirect()->back()->withInput()->with('errors', $this->discountModel->errors());
            }
            
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }

    /**
     * Halaman form edit aturan diskon
     */
    public function edit($id)
    {
        $data['discount'] = $this->discountModel->find($id);
        
        if (!$data['discount']) {
            return redirect()->to('/admin/discounts')->with('error', 'Aturan diskon tidak ditemukan.');
        }

        $data['store'] = $this->storeData;
        $data['products'] = $this->productModel->where('is_active', 1)->orderBy('nama_produk', 'ASC')->findAll();
        
        $data['selected_products'] = [];
        if (!empty($data['discount']['product_ids'])) {
            $data['selected_products'] = json_decode($data['discount']['product_ids'], true) ?? [];
        }
        
        return view('admin/discount/edit', $data);
    }

    /**
     * Proses update aturan diskon
     */
    public function update($id)
    {
        $discount = $this->discountModel->find($id);
        
        if (!$discount) {
            return redirect()->to('/admin/discounts')->with('error', 'Aturan diskon tidak ditemukan.');
        }

        $name = trim($this->request->getPost('name') ?? '');
        $discountType = $this->request->getPost('discount_type') ?? 'subtotal';
        $productIds = $this->request->getPost('product_ids') ?? [];
        $productPrices = $this->request->getPost('product_prices') ?? []; 
        $isActive = $this->request->getPost('is_active');
        $usageLimit = $this->request->getPost('usage_limit');
        $startDate = $this->request->getPost('start_date');
        $endDate = $this->request->getPost('end_date');
        $validPickupStartTime = $this->request->getPost('valid_pickup_start_time');
        $validPickupEndTime = $this->request->getPost('valid_pickup_end_time');
        $resetUsage = $this->request->getPost('reset_usage');

        $min = $this->normalizeNumber($this->request->getPost('min_amount'));
        $max = $this->request->getPost('max_amount');
        $max = ($max === '' || $max === null) ? null : $this->normalizeNumber($max);
        $pct = $this->normalizeNumber($this->request->getPost('discount_percentage'));
        $usageLimit = ($usageLimit === '' || $usageLimit === null) ? null : (int)$usageLimit;

        // Validasi Logika Tipe
        if ($discountType === 'product') {
            if (empty($productIds)) {
                return redirect()->back()->withInput()->with('error', 'Pilih minimal 1 produk.');
            }
            
            $productData = [];
            foreach ($productIds as $pId) {
                $price = isset($productPrices[$pId]) ? $this->normalizeNumber($productPrices[$pId]) : 0;
                if ($price <= 0) {
                    return redirect()->back()->withInput()->with('error', 'Harga diskon harus diisi untuk produk yang dipilih.');
                }
                $productData[$pId] = ['discounted_price' => (float)$price];
            }
            $productIdsJson = json_encode($productData);
            $min = 0; $max = 0; $pct = 0;
        } else {
            $productIdsJson = null;
            if (empty($min) || $min < 0) {
                return redirect()->back()->withInput()->with('error', 'Minimal pembelian tidak boleh kosong.');
            }
        }

        // Validasi Subtotal
        if ($discountType === 'subtotal') {
            $validation = \Config\Services::validation();
            $validation->setRules([
                'discount_percentage' => 'required|numeric|greater_than[0]|less_than_equal_to[100]',
                'is_active' => 'required|in_list[0,1]'
            ]);

            if (!$validation->run(['discount_percentage' => $pct, 'is_active' => $isActive])) {
                return redirect()->back()->withInput()->with('errors', $validation->getErrors());
            }

            if ($max !== null && $max <= $min) {
                return redirect()->back()->withInput()->with('errors', ['max_amount' => 'Maksimal pembelian harus lebih besar dari minimal pembelian']);
            }
        }

        if (!empty($startDate) && !empty($endDate) && $startDate > $endDate) {
            return redirect()->back()->withInput()->with('error', 'Tanggal mulai harus sebelum tanggal berakhir.');
        }

        $data = [
            'name' => $name ?: null,
            'discount_type' => $discountType,
            'product_ids' => $productIdsJson,
            'min_amount' => $min ?? 0,
            'max_amount' => $max ?? 0,
            'discount_percentage' => $pct ?? 0,
            'is_active' => (int) $isActive,
            'usage_limit' => $usageLimit,
            'start_date' => $startDate ?: null,
            'end_date' => $endDate ?: null,
            'valid_pickup_start_time' => $validPickupStartTime ?: null,
            'valid_pickup_end_time' => $validPickupEndTime ?: null,
        ];

        if ($resetUsage === '1') {
            $data['usage_count'] = 0;
        }

        if ($discountType === 'subtotal' && !$this->validateDiscountRange($data['min_amount'], $data['max_amount'], $id)) {
            return redirect()->back()->withInput()->with('error', 'Range pembelian bertabrakan dengan aturan diskon yang sudah ada.');
        }

        if ($this->discountModel->update($id, $data)) {
            return redirect()->to(base_url('admin/discounts'))->with('success', 'Aturan diskon berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('errors', $this->discountModel->errors());
        }
    }

    /**
     * Hapus aturan diskon
     */
    public function delete($id)
    {
        $discount = $this->discountModel->find($id);
        
        if (!$discount) {
            return redirect()->to('/admin/discounts')->with('error', 'Aturan diskon tidak ditemukan.');
        }

        if ($this->discountModel->delete($id)) {
            return redirect()->to('/admin/discounts')->with('success', 'Aturan diskon berhasil dihapus.');
        } else {
            return redirect()->to('/admin/discounts')->with('error', 'Gagal menghapus aturan diskon.');
        }
    }

    /**
     * Toggle status aktif/non-aktif aturan diskon
     */
    public function toggleStatus($id)
    {
        $this->response->setContentType('application/json');
        $discount = $this->discountModel->find($id);
        if (!$discount) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Aturan diskon tidak ditemukan.']);
        }

        $newStatus = ((int)$discount['is_active'] === 1) ? 0 : 1;

        try {
            $ok = $this->discountModel->update($id, ['is_active' => $newStatus]);
            return $this->response->setJSON([
                'status' => ($ok ? 'success' : 'error'),
                'new_status' => $newStatus,
                csrf_token() => csrf_hash(),
            ]);
        } catch (\Throwable $e) {
            return $this->response->setStatusCode(500)->setJSON(['status' => 'error']);
        }
    }

    /**
     * Validasi agar range diskon tidak overlap
     */
    private function validateDiscountRange($minAmount, $maxAmount, $excludeId = null)
    {
        $query = $this->discountModel->where('is_active', 1)->where('discount_type', 'subtotal');
        if ($excludeId) $query->where('discount_id !=', $excludeId);
        
        $existingDiscounts = $query->findAll();

        foreach ($existingDiscounts as $existing) {
            $existingMin = (float) $existing['min_amount'];
            $existingMax = (empty($existing['max_amount']) || $existing['max_amount'] == 0) ? null : (float) $existing['max_amount'];

            if ($existingMax === null) {
                if ($maxAmount === null || $maxAmount >= $existingMin) return false;
            } else {
                if ($maxAmount === null) {
                    if ($minAmount <= $existingMax) return false;
                } else {
                    if (($minAmount >= $existingMin && $minAmount <= $existingMax) ||
                        ($maxAmount >= $existingMin && $maxAmount <= $existingMax) ||
                        ($minAmount <= $existingMin && $maxAmount >= $existingMax)) {
                        return false;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Normalizes a localized numeric string to float.
     */
    private function normalizeNumber($value)
    {
        if ($value === null || $value === '') return null;
        if (is_numeric($value)) return (float) $value;
        $v = str_replace([' ', '\u00A0', '.'], '', (string)$value);
        $v = str_replace(',', '.', $v);
        return is_numeric($v) ? (float) $v : null;
    }
}