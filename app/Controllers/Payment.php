<?php

namespace App\Controllers;

use App\Models\OrderModel;
use CodeIgniter\Files\File;

class Payment extends BaseController
{
    protected $orderModel;
    protected $session;

    public function __construct()
    {
        helper(['url', 'session', 'form']);
        $this->orderModel = new OrderModel();
        $this->session = session();
    }

    // Method untuk menampilkan halaman Bank Transfer
    public function showBankTransfer($orderId)
    {
        log_message('info', '[PAYMENT DEBUG] showBankTransfer called for order {orderId}', ['orderId' => $orderId]);
        $order = $this->orderModel->find($orderId);
        if (!$order) {
            log_message('error', '[PAYMENT DEBUG] Order not found for ID: {orderId}', ['orderId' => $orderId]);
            return redirect()->to(base_url('tracking'))->with('error', 'Pesanan tidak ditemukan.');
        }
        // Jika metode pembayaran ternyata QRIS, arahkan ke halaman QRIS
        if (($order['metode_pembayaran'] ?? '') === 'QRIS') {
            return redirect()->to(base_url('checkout/qris/' . $orderId));
        }
        // Blokir hanya jika status sudah final
        if (in_array($order['status_pesanan'], ['Dibatalkan', 'Selesai', 'Dikembalikan'])) {
            return redirect()->to(base_url('tracking'))->with('error', 'Pesanan tidak aktif. Silakan cek status pesanan Anda.');
        }
        // Jika status bukan persis "Menunggu Bukti Transfer", tetap izinkan akses tapi log agar bisa ditelusuri
        if (trim((string)($order['status_pesanan'] ?? '')) !== 'Menunggu Bukti Transfer') {
            log_message('warning', '[PAYMENT DEBUG] Unexpected order status for bank transfer page. Order: {orderId}, Status: {status}', [
                'orderId' => $orderId,
                'status' => $order['status_pesanan'] ?? '(null)'
            ]);
        }

        $data['order'] = $order;
        $data['bank_details'] = [
            'BNI' => [
                'nama' => 'Bank Negara Indonesia (BNI)',
                'nomor' => '0469677820',
                'atas_nama' => 'Dannys Siburian'
            ],
           
        ];

        return view('payment_bank_transfer', $data);
    }

    // Method untuk menangani upload bukti transfer
    public function uploadProof()
    {
        $request = \Config\Services::request();
        $orderId = $request->getPost('order_id');

        if (!isset($_FILES['bukti_transfer']) || $_FILES['bukti_transfer']['error'] !== UPLOAD_ERR_OK) {
            return redirect()->back()->withInput()->with('error', 'Harap pilih file bukti transfer.');
        }

        $validationRule = [
            'bukti_transfer' => [
                'label' => 'Bukti Transfer',
                'rules' => 'uploaded[bukti_transfer]|max_size[bukti_transfer,2048]|ext_in[bukti_transfer,jpg,jpeg,png,pdf]',
                'errors' => [
                    'uploaded' => 'Harap upload file bukti transfer.',
                    'max_size' => 'Ukuran file bukti transfer maksimal 2MB.',
                    'ext_in'   => 'Format file hanya JPG, JPEG, PNG, atau PDF.'
                ]
            ],
        ];

        if (!$this->validate($validationRule)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $order = $this->orderModel->find($orderId);
        if (!$order) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        $img = $this->request->getFile('bukti_transfer');
        if ($img->isValid() && !$img->hasMoved()) {
            $newName = $orderId . '_' . $img->getRandomName();
            $uploadPath = ROOTPATH . 'public/uploads/proofs/';
            
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }

            $img->move($uploadPath, $newName);

            // GANTI NAMA KOLOM DI SINI
            $this->orderModel->update($orderId, [
                'status_pesanan' => 'Menunggu Verifikasi Admin',
                'bukti_bayar' => 'uploads/proofs/' . $newName // UBAH KE 'bukti_bayar'
            ]);

            return redirect()->to(base_url('order-success/' . $orderId))->with('success', 'Bukti transfer berhasil diupload. Pesanan Anda akan diproses setelah verifikasi.');

        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memproses upload file.');
        }
    }
}