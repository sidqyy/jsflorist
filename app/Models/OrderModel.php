<?php

namespace App\Models;

use CodeIgniter\Model;

class OrderModel extends Model
{
    protected $table        = 'orders';
    protected $primaryKey   = 'order_id';

    // 1. NONAKTIFKAN AUTO INCREMENT
    protected $useAutoIncrement = false; 

    // 2. UBAH TIPE PRIMARY KEY MENJADI STRING
    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'order_id', // PASTIKAN order_id ADA DI SINI
        'user_id',
        'tanggal_pesan',
        'status_pesanan',
        'total_harga',
        'metode_pembayaran',
        'tanggal_pengantaran',
        'tipe_pengantaran',
        'catatan_penerima',
        'penerima_nama',
        'penerima_nomor_hp',
        'alamat_pengiriman_teks',
        'alamat_latitude',
        'alamat_longitude',
        'bukti_bayar',
        'nomor_pemesan',
        'batas_waktu_pembayaran' ,
        'store_name',
        'diskon',
        'biaya_pengiriman',
        'voucher_code',
        'voucher_discount',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'tanggal_pesan';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';

    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = true;

    /**
     * Fungsi baru untuk membuat Order ID dengan format PESW001, PESW002, dst.
     */
 /**
     * Fungsi baru untuk membuat Order ID dengan format PESW001, PESW002, dst.
     * Versi ini lebih aman dan efisien.
     */
    public function generateOrderId()
    {
        // 1. Ambil nilai numerik tertinggi langsung dari kolom order_id
        $query = $this->select('MAX(CAST(SUBSTRING(order_id, 5) AS UNSIGNED)) as max_id')
                      ->get()
                      ->getRow();

        // 2. Cek apakah ada hasil dan apakah max_id tidak null
        if ($query && $query->max_id !== null) {
            $newNumber = (int)$query->max_id + 1;
        } else {
            // 3. Jika tabel kosong atau tidak ada ID yang valid, mulai dari 1
            $newNumber = 1;
        }

        // 4. Format nomor baru dengan prefix dan padding nol
        return 'PESW' . sprintf('%03d', $newNumber);
    }

    public function getOrderWithUser(int $orderId)
    {
        return $this->select('orders.*, users.nama_depan, users.nama_belakang, users.email')
                    ->join('users', 'users.user_id = orders.user_id')
                    ->where('orders.order_id', $orderId)
                    ->first();
    }
}