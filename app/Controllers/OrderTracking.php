<?php

namespace App\Controllers;

use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\ProductModel; // Untuk detail produk di riwayat pesanan

class OrderTracking extends BaseController
{
    protected $orderModel;
    protected $orderItemModel;
    protected $productModel; // Untuk mendapatkan nama produk dll.
    protected $session;

    public function __construct()
    {
        helper(['url', 'session']);
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->productModel = new ProductModel();
        $this->session = session();
    }

    // Menampilkan form pelacakan
    public function index()
    {
        return view('order_tracking_form'); // View untuk form input nomor pesanan
    }

    // Memproses pelacakan pesanan
    public function track()
    {
        $request = \Config\Services::request();
        $orderId = $request->getPost('order_id');
        $nomorPemesan = $request->getPost('nomor_pemesan');

        if (empty($orderId) || empty($nomorPemesan)) {
            return redirect()->back()->with('error', 'Nomor Pesanan dan Nomor HP Pemesan wajib diisi.');
        }

        // Cari pesanan berdasarkan order_id dan nomor HP/Email
        $order = $this->orderModel->where('order_id', $orderId)->where('nomor_pemesan', $nomorPemesan)->first();
        // Anda bisa tambahkan email juga jika ada kolom email_anda di tabel orders
        // ->where('email_customer', $emailCustomer)

        if (!$order) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan atau data tidak cocok.');
        }

        // Redirect ke halaman pembayaran jika status masih menunggu & waktu belum habis
        if ($order['status_pesanan'] === 'Menunggu Bukti Transfer') {
            $deadline = new \DateTime($order['batas_waktu_pembayaran']);
            $now = new \DateTime();

            if ($now < $deadline) {
                if ($order['metode_pembayaran'] === 'QRIS') {
                    return redirect()->to('/checkout/qris/' . $order['order_id']);
                } elseif ($order['metode_pembayaran'] === 'Direct Bank Transfer') {
                    return redirect()->to('/payment/bank-transfer/' . $order['order_id']);
                }
            }
        }

        // Ambil detail item pesanan

        $orderItems = $this->orderItemModel->where('order_id', $order['order_id'])->findAll();

        // Ambil detail produk untuk setiap item (nama, gambar)
        $detailedOrderItems = [];
        foreach ($orderItems as $item) {
            $productDetails = $this->productModel->find($item['product_id']);
            if ($productDetails) {
                $item['nama_produk'] = $productDetails['nama_produk'];
                $item['gambar_url'] = $productDetails['gambar_url'];
            } else {
                $item['nama_produk'] = 'Produk Tidak Ditemukan';
                $item['gambar_url'] = ''; // Default jika gambar tidak ada
            }
            $detailedOrderItems[] = $item;
        }

        $data['order'] = $order;
        $data['orderItems'] = $detailedOrderItems;

        return view('order_tracking_detail', $data); // View untuk menampilkan detail pesanan
    }
}
