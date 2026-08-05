<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\VoucherModel;

class VoucherController extends BaseController
{
    protected VoucherModel $voucherModel;

    public function __construct()
    {
        $this->voucherModel = new VoucherModel();
    }

    public function index()
    {
        $data['vouchers'] = $this->voucherModel->orderBy('created_at', 'DESC')->findAll();
        $data['store'] = $this->storeData;

        return view('admin/vouchers/index', $data);
    }

    public function create()
    {
        $data['store'] = $this->storeData;

        return view('admin/vouchers/create', $data);
    }

    public function store()
    {
        $code = strtoupper(trim((string) $this->request->getPost('code')));
        $name = trim((string) $this->request->getPost('name'));
        $site = trim((string) ($this->request->getPost('site') ?? 'all'));
        $discountType = trim((string) ($this->request->getPost('discount_type') ?? 'percent'));
        $discountValue = (float) ($this->request->getPost('discount_value') ?? 0);
        $pointsCost = $this->sanitizeInteger($this->request->getPost('points_cost'));
        $minAmount = $this->sanitizeNumber($this->request->getPost('min_amount'));
        $maxAmount = $this->sanitizeNumber($this->request->getPost('max_amount'));
        $expiresAt = $this->normalizeDateTime($this->request->getPost('expires_at'));
        $usageLimit = $this->sanitizeInteger($this->request->getPost('usage_limit'));
        $isActive = (int) ($this->request->getPost('is_active') ?? 1);

        $rules = [
            'code' => 'required|min_length[3]|max_length[50]',
            'name' => 'required|min_length[3]|max_length[100]',
            'site' => 'required|in_list[all,jsflorist,poppyflorist]',
            'discount_type' => 'required|in_list[percent,fixed,free_shipping]',
            'discount_value' => 'required|numeric|greater_than_equal_to[0]',
            'points_cost' => 'permit_empty|integer|greater_than_equal_to[0]',
            'min_amount' => 'permit_empty|numeric',
            'max_amount' => 'permit_empty|numeric',
            'usage_limit' => 'permit_empty|integer',
            'is_active' => 'required|in_list[0,1]'
        ];

        $payload = [
            'code' => $code,
            'name' => $name,
            'site' => $site,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'points_cost' => $pointsCost,
            'min_amount' => $minAmount,
            'max_amount' => $maxAmount,
            'usage_limit' => $usageLimit,
            'is_active' => $isActive,
        ];

        $validation = \Config\Services::validation();
        $validation->setRules($rules);

        if (!$validation->run($payload)) {
            return redirect()->to('/admin/vouchers/create')
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        if ($discountType !== 'free_shipping' && $discountValue <= 0) {
            return redirect()->to('/admin/vouchers/create')
                ->withInput()
                ->with('errors', ['discount_value' => 'Nilai diskon harus lebih besar dari 0']);
        }

        if ($maxAmount !== null && $minAmount !== null && $maxAmount <= $minAmount) {
            return redirect()->to('/admin/vouchers/create')
                ->withInput()
                ->with('errors', ['max_amount' => 'Maksimal pembelian harus lebih besar dari minimal pembelian']);
        }

        if ($this->voucherModel->where('code', $code)->first()) {
            return redirect()->to('/admin/vouchers/create')
                ->withInput()
                ->with('error', 'Kode voucher sudah ada.');
        }

        $data = [
            'code' => $code,
            'name' => $name,
            'site' => $site,
            'discount_type' => $discountType,
            'discount_value' => $discountType === 'free_shipping' ? 0 : $discountValue,
            'min_amount' => $minAmount,
            'max_amount' => $maxAmount,
            'points_cost' => $pointsCost ?? 0,
            'expires_at' => $expiresAt,
            'usage_limit' => $usageLimit,
            'is_active' => $isActive ? 1 : 0,
        ];

        if ($this->voucherModel->insert($data)) {
            return redirect()->to('/admin/vouchers')->with('success', 'Voucher berhasil dibuat.');
        }

        return redirect()->to('/admin/vouchers/create')
            ->withInput()
            ->with('error', 'Gagal membuat voucher.');
    }

    public function edit(int $id)
    {
        $data['voucher'] = $this->voucherModel->find($id);

        if (!$data['voucher']) {
            return redirect()->to('/admin/vouchers')->with('error', 'Voucher tidak ditemukan.');
        }

        $data['store'] = $this->storeData;

        return view('admin/vouchers/edit', $data);
    }

    public function update(int $id)
    {
        $voucher = $this->voucherModel->find($id);

        if (!$voucher) {
            return redirect()->to('/admin/vouchers')->with('error', 'Voucher tidak ditemukan.');
        }

        $code = strtoupper(trim((string) $this->request->getPost('code')));
        $name = trim((string) $this->request->getPost('name'));
        $site = trim((string) ($this->request->getPost('site') ?? 'all'));
        $discountType = trim((string) ($this->request->getPost('discount_type') ?? 'percent'));
        $discountValue = (float) ($this->request->getPost('discount_value') ?? 0);
        $pointsCost = $this->sanitizeInteger($this->request->getPost('points_cost'));
        $minAmount = $this->sanitizeNumber($this->request->getPost('min_amount'));
        $maxAmount = $this->sanitizeNumber($this->request->getPost('max_amount'));
        $expiresAt = $this->normalizeDateTime($this->request->getPost('expires_at'));
        $usageLimit = $this->sanitizeInteger($this->request->getPost('usage_limit'));
        $isActive = (int) ($this->request->getPost('is_active') ?? 1);

        $rules = [
            'code' => 'required|min_length[3]|max_length[50]',
            'name' => 'required|min_length[3]|max_length[100]',
            'site' => 'required|in_list[all,jsflorist,poppyflorist]',
            'discount_type' => 'required|in_list[percent,fixed,free_shipping]',
            'discount_value' => 'required|numeric|greater_than_equal_to[0]',
            'points_cost' => 'permit_empty|integer|greater_than_equal_to[0]',
            'min_amount' => 'permit_empty|numeric',
            'max_amount' => 'permit_empty|numeric',
            'usage_limit' => 'permit_empty|integer',
            'is_active' => 'required|in_list[0,1]'
        ];

        $payload = [
            'code' => $code,
            'name' => $name,
            'site' => $site,
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'points_cost' => $pointsCost,
            'min_amount' => $minAmount,
            'max_amount' => $maxAmount,
            'usage_limit' => $usageLimit,
            'is_active' => $isActive,
        ];

        $validation = \Config\Services::validation();
        $validation->setRules($rules);

        if (!$validation->run($payload)) {
            return redirect()->to('/admin/vouchers/edit/' . $id)
                ->withInput()
                ->with('errors', $validation->getErrors());
        }

        if ($discountType !== 'free_shipping' && $discountValue <= 0) {
            return redirect()->to('/admin/vouchers/edit/' . $id)
                ->withInput()
                ->with('errors', ['discount_value' => 'Nilai diskon harus lebih besar dari 0']);
        }

        if ($maxAmount !== null && $minAmount !== null && $maxAmount <= $minAmount) {
            return redirect()->to('/admin/vouchers/edit/' . $id)
                ->withInput()
                ->with('errors', ['max_amount' => 'Maksimal pembelian harus lebih besar dari minimal pembelian']);
        }

        if ($code !== '' && $code !== ($voucher['code'] ?? '')) {
            if ($this->voucherModel->where('code', $code)->first()) {
                return redirect()->to('/admin/vouchers/edit/' . $id)
                    ->withInput()
                    ->with('error', 'Kode voucher sudah ada.');
            }
        }

        $data = [
            'code' => $code,
            'name' => $name,
            'site' => $site,
            'discount_type' => $discountType,
            'discount_value' => $discountType === 'free_shipping' ? 0 : $discountValue,
            'min_amount' => $minAmount,
            'max_amount' => $maxAmount,
            'points_cost' => $pointsCost ?? 0,
            'expires_at' => $expiresAt,
            'usage_limit' => $usageLimit,
            'is_active' => $isActive ? 1 : 0,
        ];

        $this->voucherModel->update($id, $data);

        return redirect()->to('/admin/vouchers')->with('success', 'Voucher berhasil diperbarui.');
    }

    public function delete(int $id)
    {
        $voucher = $this->voucherModel->find($id);

        if (!$voucher) {
            return redirect()->to('/admin/vouchers')->with('error', 'Voucher tidak ditemukan.');
        }

        $this->voucherModel->delete($id);

        return redirect()->to('/admin/vouchers')->with('success', 'Voucher berhasil dihapus.');
    }

    public function toggleStatus(int $id)
    {
        $voucher = $this->voucherModel->find($id);

        if (!$voucher) {
            return redirect()->to('/admin/vouchers')->with('error', 'Voucher tidak ditemukan.');
        }

        $newStatus = (int) ($voucher['is_active'] ?? 0) === 1 ? 0 : 1;

        $this->voucherModel->update($id, ['is_active' => $newStatus]);

        return redirect()->to('/admin/vouchers')->with('success', 'Status voucher diperbarui.');
    }

    protected function sanitizeNumber($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (float) $value;
    }

    protected function sanitizeInteger($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    protected function normalizeDateTime($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $date = \DateTime::createFromFormat('Y-m-d\TH:i', $value);

        if ($date instanceof \DateTime) {
            return $date->format('Y-m-d H:i:s');
        }

        $date = \DateTime::createFromFormat('Y-m-d H:i:s', $value);

        if ($date instanceof \DateTime) {
            return $date->format('Y-m-d H:i:s');
        }

        return null;
    }
}