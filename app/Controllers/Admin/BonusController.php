<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\BonusRoleModel;

class BonusController extends BaseController
{
    protected $bonusModel;

    public function __construct()
    {
        $this->bonusModel = new \App\Models\BonusRoleModel();
    }

    // Tampilan Utama: Daftar Semua Aturan Promo Bonus
    public function index()
    {
        $data = [
            'title' => 'Pengaturan Bonus Promo',
            'rules' => $this->bonusModel->findAll()
        ];
        return view('admin/bonus/index', $data);
    }

    // Tampilan Form Tambah Promo Baru
    public function create()
    {
        $categoryModel = new \App\Models\CategoryModel(); 
        $productModel = new \App\Models\ProductModel(); 

        $data = [
            'title'      => 'Tambah Promo Baru',
            'products'   => $productModel->where('is_active', 1)->findAll(), 
            'categories' => $categoryModel->findAll() 
        ];
        return view('admin/bonus/create', $data);
    }

    // Proses Simpan Data dari Form ke Database
    public function store()
    {
        $ruleName      = $this->request->getPost('rule_name');
        $bonusItemName = $this->request->getPost('bonus_item_name');
        
        // Menangkap array data checkbox HTML name="product_ids[]"
        $productIdsArray = $this->request->getPost('product_ids') ?? [];
        
        // PERBAIKAN UTAMA: Hapus 'is_numeric' agar kode string seperti PRDK001 tidak ikut terbuang
        $filteredIds = array_filter(array_map('trim', $productIdsArray), function($val) {
            return $val !== '' && $val !== 'on';
        });
        
        $cleanProductIds = implode(',', $filteredIds);

        $quotaLimit    = $this->request->getPost('quota_limit');
        $startDate     = $this->request->getPost('start_date');
        $endDate       = $this->request->getPost('end_date');
        $isActive      = $this->request->getPost('is_active') ?? 0;

        $minPrices    = $this->request->getPost('min_price');
        $bonusAmounts = $this->request->getPost('bonus_amount');

        $configArray = [];
        if (!empty($minPrices) && !empty($bonusAmounts)) {
            foreach ($minPrices as $index => $price) {
                if ($price !== '' && isset($bonusAmounts[$index])) {
                    $cleanPrice = str_replace('.', '', $price); 
                    $configArray[(int)$cleanPrice] = (int)$bonusAmounts[$index];
                }
            }
        }

        $dataInsert = [
            'rule_name'              => $ruleName,
            'bonus_item_name'        => $bonusItemName,
            'applicable_product_ids' => $cleanProductIds, // Data tersimpan berupa "PRDK001,PRDK002"
            'quota_limit'            => (int)$quotaLimit,
            'usage_count'            => 0, 
            'start_date'             => $startDate,
            'end_date'               => $endDate,
            'bonus_config'           => json_encode($configArray), 
            'is_active'              => (int)$isActive
        ];

        if (empty($cleanProductIds)) {
            return redirect()->back()->withInput()->with('error', 'Gagal: Mohon pilih minimal satu produk yang berlaku!');
        }

        if ($this->bonusModel->insert($dataInsert)) {
            return redirect()->to(base_url('admin/bonus/rules'))->with('success', 'Promo berhasil ditambahkan!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menyimpan promo.');
        }
    }

    // Tampilan Form Edit Promo
    public function edit($id)
    {
        $rule = $this->bonusModel->find($id);
        if (!$rule) {
            return redirect()->to(base_url('admin/bonus/rules'))->with('error', 'Data promo tidak ditemukan.');
        }
        
        $categoryModel = new \App\Models\CategoryModel(); 
        $productModel = new \App\Models\ProductModel();

        $data = [
            'title'      => 'Edit Aturan Bonus Promo',
            'rule'       => $rule,
            'products'   => $productModel->where('is_active', 1)->findAll(), 
            'categories' => $categoryModel->findAll() 
        ];
        return view('admin/bonus/edit', $data);
    }

    // Proses Perbarui Data ke Database
    public function update($id)
    {
        $ruleName      = $this->request->getPost('rule_name');
        $bonusItemName = $this->request->getPost('bonus_item_name');
        
        // Menangkap data update checkbox array
        $productIdsArray = $this->request->getPost('product_ids') ?? [];
        
        // PERBAIKAN UTAMA: Hapus 'is_numeric' pada mode update juga agar kode text diizinkan masuk
        $filteredIds = array_filter(array_map('trim', $productIdsArray), function($val) {
            return $val !== '' && $val !== 'on';
        });
        
        $cleanProductIds = implode(',', $filteredIds);

        $quotaLimit    = $this->request->getPost('quota_limit');
        $usageCount    = $this->request->getPost('usage_count');
        $startDate     = $this->request->getPost('start_date');
        $endDate       = $this->request->getPost('end_date');
        $isActive      = $this->request->getPost('is_active') ?? 0;

        $minPrices    = $this->request->getPost('min_price');
        $bonusAmounts = $this->request->getPost('bonus_amount');

        $configArray = [];
        if (!empty($minPrices) && !empty($bonusAmounts)) {
            foreach ($minPrices as $index => $price) {
                if ($price !== '' && isset($bonusAmounts[$index])) {
                    $cleanPrice = str_replace('.', '', $price); 
                    $configArray[(int)$cleanPrice] = (int)$bonusAmounts[$index];
                }
            }
        }

        $dataUpdate = [
            'rule_name'              => $ruleName,
            'bonus_item_name'        => $bonusItemName,
            'applicable_product_ids' => $cleanProductIds, 
            'quota_limit'            => (int)$quotaLimit,
            'usage_count'            => (int)$usageCount, 
            'start_date'             => $startDate,
            'end_date'               => $endDate,
            'bonus_config'           => json_encode($configArray),
            'is_active'              => (int)$isActive
        ];

        if (empty($cleanProductIds)) {
            return redirect()->back()->withInput()->with('error', 'Gagal: Mohon pilih minimal satu produk yang berlaku!');
        }

        if ($this->bonusModel->update($id, $dataUpdate)) {
            return redirect()->to(base_url('admin/bonus/rules'))->with('success', 'Promo berhasil diperbarui!');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui promo.');
        }
    }
    
    // Fungsi Hapus Aturan Promo
    public function delete($id)
    {
        if ($this->bonusModel->delete($id)) {
            return redirect()->to(base_url('admin/bonus/rules'))->with('success', 'Promo berhasil dihapus!');
        } else {
            return redirect()->to(base_url('admin/bonus/rules'))->with('error', 'Gagal menghapus promo.');
        }
    }
}