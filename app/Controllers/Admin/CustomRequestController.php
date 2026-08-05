<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\CustomProductRequestModel;

class CustomRequestController extends BaseController
{
    public function index()
    {
        $requestModel = new CustomProductRequestModel();

        $data = [
            'requests' => $requestModel->orderBy('created_at', 'DESC')->findAll(),
        ];

        return view('admin/custom_requests/index', $data);
    }

    public function updateStatus($id)
    {
        $requestModel = new CustomProductRequestModel();
        $request = $requestModel->find($id);

        if (!$request) {
            return redirect()->back()->with('error', 'Request tidak ditemukan.');
        }

        $newStatus = $this->request->getPost('request_status');
        $available_statuses = ['Baru', 'Dikerjakan', 'Selesai', 'Dibatalkan'];

        if (!in_array($newStatus, $available_statuses)) {
            return redirect()->back()->with('error', 'Status tidak valid.');
        }

        if ($requestModel->update($id, ['request_status' => $newStatus])) {
            return redirect()->to(base_url('admin/custom-requests'))->with('success', 'Status request berhasil diperbarui.');
        } else {
            return redirect()->back()->with('error', 'Gagal memperbarui status.');
        }
    }
}
