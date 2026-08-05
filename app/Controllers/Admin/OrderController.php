<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OrderModel;
use App\Models\OrderItemModel;
use App\Models\ProductModel; // Untuk mendapatkan detail produk di order item
use App\Models\UserModel;
use App\Models\ProductComponentModel;
use App\Models\MemberModel;
use App\Models\MemberPointModel;

class OrderController extends BaseController
{
     protected $orderModel;
    protected $orderItemModel;
    protected $productModel;
    protected $userModel;
    protected $productComponentModel;
    protected $memberModel;
    protected $memberPointModel;

    public function __construct()
    {
        // Pastikan helper yang dibutuhkan dimuat
        helper(['form', 'url']);
        $this->orderModel = new OrderModel();
        $this->orderItemModel = new OrderItemModel();
        $this->productModel = new ProductModel();
        $this->userModel = new UserModel();
        $this->productComponentModel = new ProductComponentModel();
        $this->memberModel = new MemberModel();
        $this->memberPointModel = new MemberPointModel();
    }
  // Menampilkan daftar semua pesanan
    public function index()
    {
        // 1. Ambil pesanan yang belum selesai dengan pagination
        $data['incomplete_orders'] = $this->orderModel
            ->where('status_pesanan !=', 'Selesai')
            ->orderBy('tanggal_pesan', 'DESC')
            ->paginate(10, 'incomplete'); // 'incomplete' adalah nama grup pager

        // 2. Ambil pesanan yang selesai hari ini
        // Menggunakan DATE(updated_at) untuk membandingkan dengan tanggal hari ini
        $data['completed_today'] = $this->orderModel
            ->where('status_pesanan', 'Selesai')
            ->where('DATE(updated_at)', 'CURDATE()', false) // false agar CURDATE() tidak di-escape
            ->orderBy('updated_at', 'DESC')
            ->findAll();

        // 3. Ambil pager
        $data['pager'] = $this->orderModel->pager;

        return view('admin/orders/index', $data);
    }
    // Menampilkan daftar semua pesanan
public function dashboard()
{
    $orderModel = new OrderModel();
    $db = \Config\Database::connect();

    // --- PENDAPATAN HARI INI (LOGIKA DIPERBAIKI) ---
    // Logika ini juga kita perbaiki agar lebih akurat
    $pendapatanSelesaiHariIni = $orderModel->selectSum('total_harga')
                                          ->where('status_pesanan', 'Selesai')
                                          ->where('DATE(tanggal_diupdate)', date('Y-m-d')) // Berdasarkan tanggal status diubah jadi Selesai
                                          ->get()->getRow()->total_harga ?? 0;

    $pendapatanDikembalikanHariIni = ($orderModel->selectSum('total_harga')
                                              ->where('status_pesanan', 'Dikembalikan')
                                              ->where('DATE(tanggal_diupdate)', date('Y-m-d')) // Berdasarkan tanggal status diubah jadi Dikembalikan
                                              ->get()->getRow()->total_harga ?? 0) * 0.5;

    $data['pendapatan_bersih_hari_ini'] = $pendapatanSelesaiHariIni + $pendapatanDikembalikanHariIni;


    // --- STATISTIK LAIN (Tidak Berubah) ---
    $data['pesanan_baru_hari_ini'] = $orderModel->where('DATE(tanggal_pesan)', date('Y-m-d'))->countAllResults();
    $data['total_pelanggan'] = $orderModel->select('nomor_pemesan')->distinct()->countAllResults();

    // --- [PERBAIKAN FINAL] LOGIKA GRAFIK DENGAN DUA KONTEKS TANGGAL ---

    // 1. Ambil semua PENDAPATAN dari order 'Selesai' selama 7 hari terakhir, dikelompokkan per tanggal SELESAI.
    $completedBuilder = $db->table('orders');
    $completedBuilder->select("DATE(tanggal_diupdate) as tanggal, SUM(total_harga) as total");
    $completedBuilder->where('tanggal_diupdate >=', date('Y-m-d', strtotime('-6 days')));
    $completedBuilder->where('status_pesanan', 'Selesai');
    $completedBuilder->groupBy('DATE(tanggal_diupdate)');
    $completedSales = $completedBuilder->get()->getResultArray();
    
    // 2. Ambil semua POTONGAN (50%) dari order 'Dikembalikan' selama 7 hari terakhir, dikelompokkan per tanggal DIKEMBALIKAN.
    $returnedBuilder = $db->table('orders');
    $returnedBuilder->select("DATE(tanggal_diupdate) as tanggal, SUM(total_harga * 0.5) as total_potongan");
    $returnedBuilder->where('tanggal_diupdate >=', date('Y-m-d', strtotime('-6 days')));
    $returnedBuilder->where('status_pesanan', 'Dikembalikan');
    $returnedBuilder->groupBy('DATE(tanggal_diupdate)');
    $returnedSales = $returnedBuilder->get()->getResultArray();

    // 3. Petakan hasil query ke tanggal agar mudah diakses
    $revenueMap = [];
    foreach($completedSales as $sale) {
        $revenueMap[$sale['tanggal']] = (float) $sale['total'];
    }

    $deductionMap = [];
    foreach($returnedSales as $return) {
        $deductionMap[$return['tanggal']] = (float) $return['total_potongan'];
    }

    // 4. Gabungkan data di PHP untuk mendapatkan pendapatan bersih harian
    $labels = [];
    $totals = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $labels[] = date('d M', strtotime($date));

        $dailyRevenue = $revenueMap[$date] ?? 0;
        $dailyDeduction = $deductionMap[$date] ?? 0;
        
        $netDailyRevenue = $dailyRevenue - $dailyDeduction;
        $totals[] = $netDailyRevenue;
    }
    
    $data['chart_labels'] = json_encode($labels);
    $data['chart_totals'] = json_encode($totals);

    // --- PESANAN TERAKHIR (Tidak Berubah) ---
    $data['pesanan_terakhir'] = $orderModel->orderBy('tanggal_pesan', 'DESC')->limit(5)->find();

    return view('admin/dashboard', $data);
}
    // Menampilkan detail pesanan dan form untuk mengubah status
  public function detail($orderId)
    {
        $order = $this->orderModel->find($orderId);

        if (!$order) {
            return redirect()->to('/admin/orders')->with('error', 'Pesanan tidak ditemukan.');
        }

        $user = null;
        if ($order['user_id']) {
            $user = $this->userModel->find($order['user_id']);
        }

        // Ambil order items
        $orderItems = $this->orderItemModel->where('order_id', $orderId)->findAll();

        // Loop melalui order items untuk mengambil detail produk dan komponen
        foreach ($orderItems as &$item) {
            $product = $this->productModel->find($item['product_id']);
            if ($product) {
                $item['product_details'] = $product;
                // Ambil komponen untuk produk ini
                $item['components'] = $this->productComponentModel
                                        ->where('product_id', $item['product_id'])
                                        ->orderBy('sort_order', 'ASC')
                                        ->findAll();
            } else {
                // Jika produk tidak ditemukan (mungkin sudah dihapus), berikan placeholder
                $item['product_details'] = [
                    'nama_produk' => 'Produk Dihapus/Tidak Ditemukan',
                    'deskripsi_produk' => '',
                    'harga' => 0,
                    'gambar_url' => 'default_product.png' // Ganti dengan gambar default jika produk dihapus
                ];
                $item['components'] = []; // Kosongkan komponen jika produk tidak ditemukan
            }
        }
        unset($item); // Putuskan referensi dari item terakhir

        // --- START: Logika untuk mendapatkan peran pengguna yang login dari sesi ---
        // Mengambil 'role' langsung dari sesi yang sudah diatur oleh AuthController
        $currentUserRole = session()->get('role'); 
        if (empty($currentUserRole)) {
            $currentUserRole = 'guest'; // Default jika tidak ada role di sesi
        }
        // --- END: Logika untuk mendapatkan peran pengguna yang login dari sesi ---

        $data = [
            'order' => $order,
            'user' => $user,
            'orderItems' => $orderItems,
            'currentUserRole' => $currentUserRole, // <-- TERUSKAN PERAN KE VIEW
        ];

        return view('admin/orders/detail', $data);
    }
public function revenue()
{
    $orderModel = new OrderModel();
    $startDate = $this->request->getGet('start_date') ?? date('Y-m-01');
    $endDate = $this->request->getGet('end_date') ?? date('Y-m-t');

    // --- METRICS (Unaffected by pagination) ---

    // Total revenue from 'Selesai' orders
    $totalPendapatanSelesai = $orderModel->selectSum('total_harga')
                                         ->where('status_pesanan', 'Selesai')
                                         ->where('tanggal_pesan >=', $startDate . ' 00:00:00')
                                         ->where('tanggal_pesan <=', $endDate . ' 23:59:59')
                                         ->get()->getRow()->total_harga ?? 0;

    // Total value of 'Dikembalikan' orders
    $totalHargaDikembalikan = $orderModel->selectSum('total_harga')
                                          ->where('status_pesanan', 'Dikembalikan')
                                          ->where('tanggal_pesan >=', $startDate . ' 00:00:00')
                                          ->where('tanggal_pesan <=', $endDate . ' 23:59:59')
                                          ->get()->getRow()->total_harga ?? 0;

    // Total count of 'Selesai' orders for the metric card
    $totalOrdersSelesai = $orderModel->where('status_pesanan', 'Selesai')
                                     ->where('tanggal_pesan >=', $startDate . ' 00:00:00')
                                     ->where('tanggal_pesan <=', $endDate . ' 23:59:59')
                                     ->countAllResults();
    
    // Calculate net revenue and deduction (Corrected logic to match dashboard)
    $returnedRevenue = $totalHargaDikembalikan * 0.5;
    $data['total_revenue_bersih'] = $totalPendapatanSelesai + $returnedRevenue;
    $data['total_deduction'] = $returnedRevenue;


    // --- PAGINATED DATA ---

    // Completed Orders
    $completedOrderModel = new OrderModel(); // Use a new model instance for isolation
    $data['completed_orders'] = $completedOrderModel
        ->where('status_pesanan', 'Selesai')
        ->where('tanggal_pesan >=', $startDate . ' 00:00:00')
        ->where('tanggal_pesan <=', $endDate . ' 23:59:59')
        ->orderBy('tanggal_pesan', 'DESC')
        ->paginate(10, 'completed');
    $data['pager_completed'] = $completedOrderModel->pager;

    // Returned Orders
    $returnedOrderModel = new OrderModel(); // Use another new model instance
    $data['returned_orders'] = $returnedOrderModel
        ->where('status_pesanan', 'Dikembalikan')
        ->where('tanggal_pesan >=', $startDate . ' 00:00:00')
        ->where('tanggal_pesan <=', $endDate . ' 23:59:59')
        ->orderBy('tanggal_pesan', 'DESC')
        ->paginate(10, 'returned');
    $data['pager_returned'] = $returnedOrderModel->pager;
    
    // --- FINAL DATA FOR VIEW ---
    $data['total_orders_selesai'] = $totalOrdersSelesai;
    $data['average_order_value'] = ($totalOrdersSelesai > 0) ? $totalPendapatanSelesai / $totalOrdersSelesai : 0;
    $data['start_date'] = $startDate;
    $data['end_date'] = $endDate;

    return view('admin/revenue/index', $data);
}

    // Mengupdate status pesanan
    public function updateStatus($orderId)
    {
        $order = $this->orderModel->find($orderId);

        if (!$order) {
            return redirect()->back()->with('error', 'Pesanan tidak ditemukan.');
        }

        $newStatus = $this->request->getPost('status_pesanan');

        // Validasi status baru
        $availableStatuses = [
            'Menunggu Bukti Transfer',
            'Menunggu Verifikasi Admin',
            'Dikonfirmasi',
            'Diproses',
            'Siap Dikirim/Diambil',
            'Dalam Pengiriman',
            'Selesai',
            'Dibatalkan',
            'Dikembalikan'
        ];

        if (!in_array($newStatus, $availableStatuses)) {
            return redirect()->back()->with('error', 'Status yang dipilih tidak valid.');
        }

        $data = ['status_pesanan' => $newStatus];

        if ($this->orderModel->update($orderId, $data)) {
            if ($newStatus === 'Selesai' && !empty($order['user_id'])) {
                $this->awardPointsForCompletedOrder($orderId, (int) $order['user_id']);
            }
            return redirect()->to(base_url('admin/orders/detail/' . $orderId))->with('success', 'Status pesanan berhasil diperbarui.');
        } else {
            return redirect()->to(base_url('admin/orders/detail/' . $orderId))->with('error', 'Gagal memperbarui status pesanan.');
        }
    }

    protected function awardPointsForCompletedOrder(string $orderId, int $userId): void
    {
        $existing = $this->memberPointModel
            ->where('source', 'order')
            ->where('reference_id', 'order:' . $orderId)
            ->first();

        if ($existing) {
            return;
        }

        $member = $this->memberModel->findByUserId($userId);
        if (!$member) {
            $memberId = $this->memberModel->insert([
                'user_id' => $userId,
                'member_code' => $this->generateMemberCode($userId),
                'tier' => 'regular',
                'points_balance' => 0,
                'total_points_earned' => 0,
                'total_points_redeemed' => 0,
                'status' => 1,
                'joined_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ], true);
            $member = $this->memberModel->find($memberId);
        }

        if (!$member) {
            return;
        }

        $subtotalRow = $this->orderItemModel
            ->select('SUM(kuantitas * harga_satuan) as subtotal')
            ->where('order_id', $orderId)
            ->first();

        $subtotal = (float) ($subtotalRow['subtotal'] ?? 0);
        if ($subtotal <= 0) {
            return;
        }

        $points = (int) floor($subtotal / 1000) * 10;
        if ($points <= 0) {
            return;
        }

        $now = date('Y-m-d H:i:s');
        $this->memberPointModel->insert([
            'member_id' => $member['member_id'],
            'points' => $points,
            'type' => 'earn',
            'source' => 'order',
            'reference_id' => 'order:' . $orderId,
            'note' => 'Poin dari pembelian (status selesai)',
            'created_at' => $now,
        ]);

        $this->memberModel->update($member['member_id'], [
            'points_balance' => (int) $member['points_balance'] + $points,
            'total_points_earned' => (int) $member['total_points_earned'] + $points,
            'updated_at' => $now,
        ]);

        $this->sendPointsEmailNotification($userId, $points, $orderId);
    }

    protected function generateMemberCode(int $userId): string
    {
        $seed = strtoupper(substr(md5($userId . microtime(true)), 0, 6));
        return 'MBR' . str_pad((string) $userId, 4, '0', STR_PAD_LEFT) . $seed;
    }

    protected function sendPointsEmailNotification(int $userId, int $points, string $orderId): void
    {
        $user = $this->userModel->find($userId);
        if (!$user || empty($user['email'])) {
            return;
        }

        $emailService = service('email');
        $emailService->setTo($user['email']);
        $emailService->setSubject('Poin Member Bertambah');
        $emailService->setMessage($this->buildPointsEmailBody($user['username'] ?? 'Member', $points, $orderId));

        $emailService->send();
    }

    protected function buildPointsEmailBody(string $username, int $points, string $orderId): string
    {
        return "Halo {$username},\n\n" .
            "Poin Anda bertambah {$points} poin dari pesanan {$orderId}.\n" .
            "Terima kasih sudah berbelanja di JS Florist.\n\n" .
            "Salam,\nJS Florist";
    }

   public function productAnalysis()
{
    $db = \Config\Database::connect();

    // --- Query untuk Produk Terlaris (Tidak Berubah) ---
    $builder = $db->table('order_items');
    $builder->select('order_items.product_id, products.nama_produk, products.gambar_url, SUM(order_items.kuantitas) as total_terjual, SUM(order_items.kuantitas * order_items.harga_satuan) as total_pendapatan');
    $builder->join('products', 'products.product_id = order_items.product_id'); // Sesuaikan dengan primary key Anda
    $builder->join('orders', 'orders.order_id = order_items.order_id');
    $builder->where('orders.status_pesanan', 'Selesai');
    $builder->groupBy('order_items.product_id, products.nama_produk, products.gambar_url');
    $builder->orderBy('total_terjual', 'DESC');
    $builder->limit(10); 

    $data['produk_terlaris'] = $builder->get()->getResultArray();

    // --- [FITUR BARU] Query untuk Analisis Kategori ---
    $categoryBuilder = $db->table('order_items');
    $categoryBuilder->select('categories.nama_kategori, COUNT(DISTINCT orders.order_id) as jumlah_transaksi, SUM(order_items.kuantitas * order_items.harga_satuan) as total_pendapatan_kategori');
    $categoryBuilder->join('products', 'products.product_id = order_items.product_id');
    $categoryBuilder->join('categories', 'categories.category_id = products.category_id');
    $categoryBuilder->join('orders', 'orders.order_id = order_items.order_id');
    $categoryBuilder->where('orders.status_pesanan', 'Selesai');
    $categoryBuilder->groupBy('categories.category_id, categories.nama_kategori');
    $categoryBuilder->orderBy('total_pendapatan_kategori', 'DESC');

    $data['analisis_kategori'] = $categoryBuilder->get()->getResultArray();

    return view('admin/products/analysis', $data);
}
    
}
