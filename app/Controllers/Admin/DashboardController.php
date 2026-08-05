<?php

namespace App\Controllers\Admin;
use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\CustomRequestModel;
use App\Models\UserModel; // Asumsikan Anda punya model untuk user/pelanggan

class DashboardController extends BaseController
{
 public function dashboard()
{
    // Inisialisasi model
    $orderModel = new OrderModel();

    // 1. Ambil data untuk "Statistik Kunci" (KPIs)
    
    // [PERBAIKAN] Pendapatan hanya dihitung dari pesanan yang statusnya 'Selesai'
    $data['pendapatan_hari_ini'] = $orderModel->selectSum('total_harga')
                                             ->where('DATE(tanggal_pesan)', date('Y-m-d'))
                                             ->where('status_pesanan', 'Selesai') // <-- Diubah di sini
                                             ->get()->getRow()->total_harga ?? 0;

    $data['pesanan_baru_hari_ini'] = $orderModel->where('DATE(tanggal_pesan)', date('Y-m-d'))
                                               ->countAllResults();
    
    $data['total_pelanggan'] = $orderModel->select('nomor_pemesan')
                                         ->distinct()
                                         ->countAllResults();

    // 2. Ambil data untuk "Grafik Penjualan" dengan rentang tanggal dinamis
    // Baca start_date dan end_date dari query string (GET)
    $startDate = $this->request->getGet('start_date');
    $endDate   = $this->request->getGet('end_date');

    // Validasi sederhana dan default: 7 hari terakhir
    $today = date('Y-m-d');
    if (!$endDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $endDate)) {
        $endDate = $today;
    }
    if (!$startDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) {
        $startDate = date('Y-m-d', strtotime('-6 days', strtotime($endDate)));
    }

    // Jika start > end, tukar agar aman
    if (strtotime($startDate) > strtotime($endDate)) {
        [$startDate, $endDate] = [$endDate, $startDate];
    }

    // Batasi maksimal range (opsional, misal 365 hari) agar query tetap ringan
    $maxRangeDays = 366; // inklusif
    $diffDays = (int) floor((strtotime($endDate) - strtotime($startDate)) / 86400) + 1;
    if ($diffDays > $maxRangeDays) {
        $startDate = date('Y-m-d', strtotime("-$maxRangeDays days", strtotime($endDate)));
    }

    // Ambil data agregat per hari untuk rentang tanggal
    $salesData = $orderModel->select("DATE(tanggal_pesan) as tanggal, SUM(total_harga) as total")
                            ->where('DATE(tanggal_pesan) >=', $startDate)
                            ->where('DATE(tanggal_pesan) <=', $endDate)
                            ->where('status_pesanan', 'Selesai')
                            ->groupBy('DATE(tanggal_pesan)')
                            ->orderBy('tanggal', 'ASC')
                            ->get()->getResultArray();

    // Proses data agar siap digunakan oleh Chart.js sesuai range terpilih
    $labels = [];
    $totals = [];
    $cursor = strtotime($startDate);
    $endTs  = strtotime($endDate);
    // Map hasil query untuk lookup cepat
    $map = [];
    foreach ($salesData as $row) {
        $map[$row['tanggal']] = (float) $row['total'];
    }
    while ($cursor <= $endTs) {
        $date = date('Y-m-d', $cursor);
        $labels[] = date('d M', $cursor);
        $totals[] = $map[$date] ?? 0;
        $cursor = strtotime('+1 day', $cursor);
    }

    $data['chart_labels'] = json_encode($labels);
    $data['chart_totals'] = json_encode($totals);
    $data['start_date']    = $startDate;
    $data['end_date']      = $endDate;

    // 3. Ambil data untuk "5 Pesanan Terakhir" (tidak ada perubahan di sini)
    $data['pesanan_terakhir'] = $orderModel->orderBy('tanggal_pesan', 'DESC')
                                           ->limit(5)
                                           ->find();

    // Kirim semua data ke view
    return view('admin/dashboard', $data);
}

    /**
     * Endpoint for AJAX polling to check for new orders.
     * Returns a JSON object with the total order count.
     */
    public function checkNewOrders()
    {
        // Ensure this is an AJAX request for security
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(403, 'Forbidden');
        }

        $orderModel = new OrderModel();
        $orderCount = $orderModel->countAllResults();

        return $this->response->setJSON(['order_count' => $orderCount]);
    }
}
