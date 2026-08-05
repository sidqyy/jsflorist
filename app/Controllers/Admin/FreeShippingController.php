<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\FreeShippingRuleModel;

class FreeShippingController extends BaseController
{

    public function __construct()
    {
        $this->ruleModel = new FreeShippingRuleModel();
    }

    public function index()
    {
        $data['rules'] = $this->ruleModel->orderBy('min_amount', 'ASC')->findAll();
        $data['store'] = $this->storeData;
        return view('admin/free_shipping/index', $data);
    }

   public function create()
    {
        $productModel = new ProductModel();
        
        // Sesuaikan: orderBy pakai 'nama_produk' sesuai isi ProductModel kamu
        $data['products'] = $productModel->orderBy('nama_produk', 'ASC')->findAll(); 
        $data['store'] = $this->storeData;
    
        return view('admin/free_shipping/create', $data);
    }

   public function store()
    {
        $applyToAll = $this->request->getPost('apply_to_all');
        $productIds = $this->request->getPost('product_ids'); 
        $startDate  = $this->request->getPost('start_date');
        $endDate    = $this->request->getPost('end_date');
        
        $min = (float) $this->request->getPost('min_amount');
        $max = $this->request->getPost('max_amount');
        $max = ($max === '' || $max === null) ? null : (float) $max;
        $maxDist = $this->request->getPost('max_distance_km');
        $maxDist = ($maxDist === '' || $maxDist === null) ? null : (float) $maxDist;
        $isActive = $this->request->getPost('is_active');

        $errors = [];
        if (empty($startDate)) $errors['start_date'] = 'Tanggal mulai harus diisi.';
        if (empty($endDate)) $errors['end_date'] = 'Tanggal berakhir harus diisi.';
        if ($applyToAll == '0' && empty($productIds)) {
            $errors['product_ids'] = 'Pilih minimal satu produk.';
        }

        if (!empty($errors)) {
            return redirect()->back()->withInput()->with('errors', $errors);
        }

        $data = [
            'min_amount' => $min,
            'max_amount' => $max,
            'max_distance_km' => $maxDist,
            'is_active' => (int) $isActive,
            'apply_to_all'    => (int) $applyToAll,
            'start_date'      => $startDate,
            'end_date'        => $endDate,
            // Jika apply_to_all = 1, product_ids dikosongkan. 
            // Jika 0, array produk digabung jadi string (comma separated)
            'product_ids'     => ($applyToAll == '1') ? null : implode(',', $productIds),
        ];

        if ($this->ruleModel->insert($data)) {
            return redirect()->to('/admin/free-shipping')->with('success', 'Rule gratis ongkir ditambahkan.');
        }
        return redirect()->back()->withInput()->with('error', 'Gagal menambahkan rule.');
    }

   public function edit($id)
{
    $rule = $this->ruleModel->find($id);
    if (!$rule) {
        return redirect()->to('/admin/free-shipping')->with('error', 'Rule tidak ditemukan.');
    }

    // Inisialisasi model produk untuk list pilihan di view
    $productModel = new \App\Models\ProductModel(); 
    
    $data['rule'] = $rule;
    $data['store'] = $this->storeData;
    $data['products'] = $productModel->orderBy('nama_produk', 'ASC')->findAll();
    
    // Konversi string "1,2,3" dari DB menjadi array [1, 2, 3]
    // Ini penting supaya di View kita bisa cek mana produk yang sudah dicentang
    $data['selectedProducts'] = $rule['product_ids'] ? explode(',', $rule['product_ids']) : [];

    return view('admin/free_shipping/edit', $data);
}
    public function update($id)
{
    $rule = $this->ruleModel->find($id);
    if (!$rule) {
        return redirect()->to('/admin/free-shipping')->with('error', 'Rule tidak ditemukan.');
    }

    // Mengambil data input standar
    $min = (float) $this->request->getPost('min_amount');
    $max = $this->request->getPost('max_amount');
    $max = ($max === '' || $max === null) ? null : (float) $max;
    $maxDist = $this->request->getPost('max_distance_km');
    $maxDist = ($maxDist === '' || $maxDist === null) ? null : (float) $maxDist;
    $isActive = $this->request->getPost('is_active');

    // Mengambil data input baru (Produk & Waktu)
    $applyToAll = $this->request->getPost('apply_to_all');
    $productIds = $this->request->getPost('product_ids'); // Berupa array dari form
    $startDate  = $this->request->getPost('start_date');
    $endDate    = $this->request->getPost('end_date');

    // Validasi
    $errors = [];
    if (!is_numeric($min) || $min < 0) $errors['min_amount'] = 'Minimal subtotal tidak valid.';
    if ($max !== null && (!is_numeric($max) || $max < $min)) $errors['max_amount'] = 'Maksimal subtotal harus >= minimal subtotal atau kosong.';
    if ($maxDist !== null && (!is_numeric($maxDist) || $maxDist < 0)) $errors['max_distance_km'] = 'Maksimal jarak tidak valid.';
    if (!in_array($isActive, ['0','1',0,1], true)) $errors['is_active'] = 'Status tidak valid.';
    
    // Validasi tambahan untuk fitur baru
    if (empty($startDate)) $errors['start_date'] = 'Tanggal mulai harus diisi.';
    if (empty($endDate)) $errors['end_date'] = 'Tanggal berakhir harus diisi.';
    if ($applyToAll == '0' && empty($productIds)) {
        $errors['product_ids'] = 'Silakan pilih minimal satu produk jika tidak berlaku untuk semua.';
    }

    if (!empty($errors)) {
        return redirect()->back()->withInput()->with('errors', $errors);
    }

    // Menyiapkan data untuk diupdate ke database
    $data = [
        'min_amount'      => $min,
        'max_amount'      => $max,
        'max_distance_km' => $maxDist,
        'is_active'       => (int) $isActive,
        'apply_to_all'    => (int) $applyToAll,
        'start_date'      => $startDate,
        'end_date'        => $endDate,
        // Jika apply_to_all = 1, product_ids dikosongkan. 
        // Jika 0, array ID produk digabung menjadi string dipisahkan koma.
        'product_ids'     => ($applyToAll == '1') ? null : (is_array($productIds) ? implode(',', $productIds) : null),
    ];

    if ($this->ruleModel->update($id, $data)) {
        return redirect()->to('/admin/free-shipping')->with('success', 'Rule berhasil diperbarui.');
    }
    return redirect()->back()->withInput()->with('error', 'Gagal memperbarui rule.');
}

    public function delete($id)
    {
        $rule = $this->ruleModel->find($id);
        if (!$rule) {
            return redirect()->to('/admin/free-shipping')->with('error', 'Rule tidak ditemukan.');
        }
        if ($this->ruleModel->delete($id)) {
            return redirect()->to('/admin/free-shipping')->with('success', 'Rule dihapus.');
        }
        return redirect()->to('/admin/free-shipping')->with('error', 'Gagal menghapus rule.');
    }

    public function toggleStatus($id)
    {
        $this->response->setContentType('application/json');
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Request harus AJAX.']);
        }

        $rule = $this->ruleModel->find($id);
        if (!$rule) {
            return $this->response->setJSON(['status' => 'error', 'message' => 'Rule tidak ditemukan.']);
        }

        $newStatus = $rule['is_active'] == 1 ? 0 : 1;
        $ok = $this->ruleModel->skipValidation(true)->update($id, ['is_active' => $newStatus]);
        if ($ok) {
            return $this->response->setJSON(['status' => 'success', 'new_status' => $newStatus]);
        }
        return $this->response->setStatusCode(500)->setJSON(['status' => 'error', 'message' => 'Gagal mengubah status.']);
    }
}
