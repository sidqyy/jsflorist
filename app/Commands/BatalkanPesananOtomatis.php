<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\OrderModel; // Pastikan namespace dan nama model sudah benar

class BatalkanPesananOtomatis extends BaseCommand
{
    /**
     * The Command's Group.
     *
     * @var string
     */
    protected $group = 'Pesanan';

    /**
     * The Command's Name.
     *
     * @var string
     */
    protected $name = 'pesanan:batalkan_otomatis';

    /**
     * The Command's Description.
     *
     * @var string
     */
    protected $description = 'Mengecek dan membatalkan pesanan yang melewati batas waktu pembayaran.';

    /**
     * The Command's Usage.
     *
     * @var string
     */
    protected $usage = 'pesanan:batalkan_otomatis';

    /**
     * The Command's Arguments.
     *
     * @var array
     */
    protected $arguments = [];

    /**
     * The Command's Options.
     *
     * @var array
     */
    protected $options = [];

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        CLI::write('Mulai menjalankan tugas pembatalan pesanan otomatis...', 'green');

        $orderModel = new OrderModel();
        
        // Dapatkan waktu saat ini sesuai timezone server
        $now = date('Y-m-d H:i:s');

        // Cari pesanan yang:
        // 1. Statusnya 'Menunggu Pembayaran'
        // 2. Waktu `batas_waktu_pembayaran` sudah lewat dari sekarang
        $expiredOrders = $orderModel
            ->where('status_pesanan', 'Menunggu Bukti Transfer')
            ->where('batas_waktu_pembayaran <=', $now)
            ->findAll();

        if (empty($expiredOrders)) {
            CLI::write('Tidak ada pesanan kedaluwarsa yang ditemukan.', 'yellow');
            return;
        }

        $cancelledCount = 0;
        foreach ($expiredOrders as $order) {
            $orderId = $order['order_id']; // Gunakan order_id atau primary key Anda
            
            // Update status pesanan menjadi 'Dibatalkan'
            $orderModel->update($order['order_id'], ['status_pesanan' => 'Dibatalkan Sistem']);
            
            CLI::write("Pesanan #{$orderId} telah dibatalkan.", 'cyan');
            $cancelledCount++;
        }

        CLI::write("Selesai. Total {$cancelledCount} pesanan telah berhasil dibatalkan.", 'green');
    }
}