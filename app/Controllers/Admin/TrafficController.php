<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\TrafficLogModel;

/**
 * Controller untuk menampilkan analisis traffic pengunjung di panel admin
 */
class TrafficController extends BaseController
{
    protected $trafficModel;

    public function __construct()
    {
        $this->trafficModel = new TrafficLogModel();
    }

    /**
     * Halaman utama analisis traffic
     * 
     * @return string
     */
    public function index()
    {
        // Ambil data statistik untuk dashboard
        $data = [
            'title' => 'Analisis Traffic Pengunjung',
            'todayVisits' => $this->trafficModel->getTodayVisits(),
            'yesterdayVisits' => $this->trafficModel->getYesterdayVisits(),
            'monthlyVisits' => $this->trafficModel->getMonthlyVisits(),
            'hourlyTraffic' => $this->trafficModel->getHourlyTraffic(),
            'dailyTraffic' => $this->trafficModel->getDailyTraffic(),
            'topCountries' => $this->trafficModel->getTopCountries(10),
        ];

        // Hitung persentase perubahan dari kemarin
        if ($data['yesterdayVisits'] > 0) {
            $data['todayChangePercent'] = round((($data['todayVisits'] - $data['yesterdayVisits']) / $data['yesterdayVisits']) * 100, 1);
        } else {
            $data['todayChangePercent'] = $data['todayVisits'] > 0 ? 100 : 0;
        }

        // Persiapkan data untuk Chart.js
        $data['hourlyChartData'] = $this->prepareHourlyChartData($data['hourlyTraffic']);
        $data['dailyChartData'] = $this->prepareDailyChartData($data['dailyTraffic']);

        return view('admin/traffic/index', $data);
    }

    /**
     * Halaman analisis halaman website
     * 
     * @return string
     */
    public function pages()
    {
        $days = (int)($this->request->getGet('days') ?? 30);
        
        $data = [
            'title' => 'Analisis Halaman Website',
            'days' => $days,
            'popular_pages' => $this->trafficModel->getPopularPages(15, $days),
            'entry_pages' => $this->trafficModel->getEntryPages(10, $days),
            'traffic_sources' => $this->trafficModel->getTrafficSources(10, $days),
            'browser_stats' => $this->trafficModel->getBrowserStats(10),
        ];
        
        return view('admin/traffic/pages', $data);
    }

    /**
     * Test method untuk cek page tracking
     * 
     * @return string
     */
    public function testTracking()
    {
        // Simulasi visitor log dengan data page
        $testData = [
            [
                'ip_address' => '203.78.121.168',
                'country' => 'Indonesia',
                'city' => 'Jakarta',
                'access_date' => date('Y-m-d'),
                'access_time' => date('H:i:s'),
                'page_url' => '/',
                'page_title' => 'Homepage',
                'user_agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                'referer' => '',
            ],
            [
                'ip_address' => '114.79.18.236',
                'country' => 'Indonesia',
                'city' => 'Surabaya',
                'access_date' => date('Y-m-d'),
                'access_time' => date('H:i:s'),
                'page_url' => '/shop',
                'page_title' => 'Shop - Katalog Produk',
                'user_agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 14_0 like Mac OS X)',
                'referer' => 'https://google.com',
            ],
            [
                'ip_address' => '180.244.139.125',
                'country' => 'Indonesia', 
                'city' => 'Bandung',
                'access_date' => date('Y-m-d'),
                'access_time' => date('H:i:s'),
                'page_url' => '/shop/category/1',
                'page_title' => 'Kategori - 1',
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7)',
                'referer' => 'https://facebook.com',
            ]
        ];

        $inserted = 0;
        foreach ($testData as $data) {
            if ($this->trafficModel->insert($data)) {
                $inserted++;
            }
        }

        return "Test tracking completed. Inserted {$inserted} sample records with page data.";
    }

    /**
     * Lihat detail referer untuk analisis sumber traffic
     * 
     * @return string
     */
    public function refererDetails()
    {
        $referers = $this->trafficModel->select('referer, COUNT(*) as count')
                                     ->where('referer !=', '')
                                     ->where('referer IS NOT NULL')
                                     ->groupBy('referer')
                                     ->orderBy('count', 'DESC')
                                     ->limit(20)
                                     ->findAll();

        $html = '<h3>Detail Referer Traffic</h3><table border="1" style="border-collapse: collapse; width: 100%;">';
        $html .= '<tr><th>Referer URL</th><th>Count</th><th>Source</th></tr>';
        
        foreach ($referers as $row) {
            $referer = $row['referer'];
            $source = 'Other';
            
            if (strpos(strtolower($referer), 'google.') !== false) {
                $source = 'Google';
            } elseif (strpos(strtolower($referer), 'facebook.') !== false) {
                $source = 'Facebook';
            } elseif (strpos(strtolower($referer), 'twitter.') !== false || strpos(strtolower($referer), 't.co') !== false) {
                $source = 'Twitter';
            } elseif (strpos(strtolower($referer), 'instagram.') !== false) {
                $source = 'Instagram';
            }
            
            $html .= '<tr>';
            $html .= '<td>' . esc($referer) . '</td>';
            $html .= '<td>' . $row['count'] . '</td>';
            $html .= '<td><strong>' . $source . '</strong></td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        return $html;
    }

    /**
     * Persiapkan data untuk chart hourly traffic
     * 
     * @param array $hourlyData
     * @return array
     */
    private function prepareHourlyChartData(array $hourlyData): array
    {
        // Buat array untuk 24 jam (0-23)
        $hours = array_fill(0, 24, 0);
        
        // Isi data yang ada
        foreach ($hourlyData as $data) {
            $hour = (int)$data['hour'];
            $hours[$hour] = (int)$data['visits'];
        }

        return [
            'labels' => array_map(fn($h) => sprintf('%02d:00', $h), range(0, 23)),
            'data' => array_values($hours),
        ];
    }

    /**
     * Persiapkan data untuk chart daily traffic
     * 
     * @param array $dailyData
     * @return array
     */
    private function prepareDailyChartData(array $dailyData): array
    {
        $labels = [];
        $data = [];

        foreach ($dailyData as $day) {
            $labels[] = date('M d', strtotime($day['date']));
            $data[] = (int)$day['visits'];
        }

        return [
            'labels' => $labels,
            'data' => $data,
        ];
    }

    /**
     * API endpoint untuk mendapatkan data traffic real-time
     * 
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function apiData()
    {
        $type = $this->request->getGet('type') ?? 'hourly';

        switch ($type) {
            case 'hourly':
                $data = $this->trafficModel->getHourlyTraffic();
                return $this->response->setJSON($this->prepareHourlyChartData($data));
                
            case 'daily':
                $data = $this->trafficModel->getDailyTraffic();
                return $this->response->setJSON($this->prepareDailyChartData($data));
                
            case 'countries':
                $data = $this->trafficModel->getTopCountries(10);
                return $this->response->setJSON($data);
                
            case 'stats':
                return $this->response->setJSON([
                    'today' => $this->trafficModel->getTodayVisits(),
                    'yesterday' => $this->trafficModel->getYesterdayVisits(),
                    'monthly' => $this->trafficModel->getMonthlyVisits(),
                ]);
                
            default:
                return $this->response->setStatusCode(400)->setJSON(['error' => 'Invalid type parameter']);
        }
    }

    /**
     * Debug endpoint untuk melihat data traffic logs terbaru
     * 
     * @return string
     */
    public function debug()
    {
        $db = \Config\Database::connect();
        $query = $db->query('SELECT * FROM traffic_logs ORDER BY created_at DESC LIMIT 30');
        $results = $query->getResultArray();
        
        echo "<h2>Debug Traffic Logs (30 terakhir):</h2>";
        echo "<style>
            table { border-collapse: collapse; width: 100%; font-size: 12px; }
            th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
            th { background-color: #f2f2f2; }
            .bot-likely { background-color: #fff3cd; }
            .human-likely { background-color: #d4edda; }
        </style>";
        
        echo "<table>";
        echo "<tr><th>ID</th><th>IP</th><th>Country</th><th>City</th><th>Date</th><th>Time</th><th>Created At</th><th>Analysis</th></tr>";
        
        foreach ($results as $row) {
            // Analisis sederhana untuk mendeteksi kemungkinan bot
            $isLikelyBot = $this->trafficModel->analyzePotentialBot($row['ip_address'], $row['country']);
            $rowClass = $isLikelyBot ? 'bot-likely' : 'human-likely';
            
            echo "<tr class='{$rowClass}'>";
            echo "<td>{$row['id']}</td>";
            echo "<td>{$row['ip_address']}</td>";
            echo "<td>{$row['country']}</td>";
            echo "<td>{$row['city']}</td>";
            echo "<td>{$row['access_date']}</td>";
            echo "<td>{$row['access_time']}</td>";
            echo "<td>{$row['created_at']}</td>";
            echo "<td>" . ($isLikelyBot ? '🤖 Likely Bot/Crawler' : '👤 Likely Human') . "</td>";
            echo "</tr>";
        }
        
        echo "</table>";
        
        // Statistik
        echo "<h3>Analysis Summary:</h3>";
        $countryStats = [];
        $botCount = 0;
        $humanCount = 0;
        
        foreach ($results as $row) {
            $country = $row['country'];
            if (!isset($countryStats[$country])) {
                $countryStats[$country] = 0;
            }
            $countryStats[$country]++;
            
            if ($this->trafficModel->analyzePotentialBot($row['ip_address'], $row['country'])) {
                $botCount++;
            } else {
                $humanCount++;
            }
        }
        
        echo "<div style='display: flex; gap: 20px;'>";
        echo "<div>";
        echo "<h4>By Country:</h4>";
        foreach ($countryStats as $country => $count) {
            echo "<p>{$country}: {$count} visits</p>";
        }
        echo "</div>";
        
        echo "<div>";
        echo "<h4>Visitor Type:</h4>";
        echo "<p>🤖 Likely Bots: {$botCount}</p>";
        echo "<p>👤 Likely Humans: {$humanCount}</p>";
        echo "<p>Total Records: " . count($results) . "</p>";
        echo "</div>";
        echo "</div>";
        
        echo "<h3>Debug Info:</h3>";
        echo "<p>Current IP: " . $this->request->getIPAddress() . "</p>";
        echo "<p>Current URI: " . current_url() . "</p>";
        echo "<p>Session Role: " . (session()->get('role') ?? 'Not logged in') . "</p>";
        echo "<p>Is Admin Session: " . (session()->get('isLoggedIn') ? 'Yes (not tracked)' : 'No') . "</p>";
        
        echo "<div style='background: #fff3cd; padding: 10px; border-radius: 5px; margin: 10px 0;'>";
        echo "<strong>Note:</strong> Admin/Management users are not tracked in traffic logs.";
        echo "</div>";
        
        echo "<p><a href='" . base_url('admin/traffic') . "' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>← Back to Traffic Analysis</a></p>";
        echo "<p><a href='" . base_url('admin/traffic/cleanup') . "' style='background: #dc3545; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>🧹 Cleanup Admin Data</a></p>";
        
        return '';
    }

    /**
     * Halaman untuk melihat semua log pengunjung dengan filter dan pagination
     * 
     * @return string
     */
    public function logs()
    {
        $page = (int)($this->request->getGet('page') ?? 1);
        $perPage = 25;
        
        // Ambil filter dari request
        $filters = [
            'country' => $this->request->getGet('country') ?? '',
            'city' => $this->request->getGet('city') ?? '',
            'date_from' => $this->request->getGet('date_from') ?? '',
            'date_to' => $this->request->getGet('date_to') ?? '',
            'ip' => $this->request->getGet('ip') ?? '',
        ];
        
        // Hapus filter kosong
        $filters = array_filter($filters);
        
        // Ambil data pengunjung dengan pagination
        $visitorsData = $this->trafficModel->getVisitorsPaginated($page, $perPage, $filters);
        
        // Analisis setiap visitor untuk deteksi bot
        $visitors = $visitorsData['data'];
        foreach ($visitors as &$visitor) {
            $visitor['is_likely_bot'] = $this->trafficModel->analyzePotentialBot($visitor['ip_address'], $visitor['country']);
        }
        
        // Ambil data untuk dropdown filter
        $availableCountries = $this->trafficModel->getAvailableCountries();
        $availableCities = [];
        
        if (!empty($filters['country'])) {
            $availableCities = $this->trafficModel->getCitiesByCountry($filters['country']);
        }
        
        // Generate pagination URLs
        $paginationUrls = $this->generatePaginationUrls($visitorsData, $filters);
        
        $data = [
            'title' => 'Log Pengunjung Website',
            'visitors' => $visitors,
            'pagination' => $visitorsData,
            'pagination_urls' => $paginationUrls,
            'filters' => $filters,
            'countries' => $availableCountries,
            'cities' => $availableCities,
        ];
        
        return view('admin/traffic/logs', $data);
    }

    /**
     * API endpoint untuk mendapatkan kota berdasarkan negara (untuk filter dinamis)
     */
    public function getCities()
    {
        $country = $this->request->getGet('country');
        
        if (empty($country)) {
            return $this->response->setJSON([]);
        }
        
        $cities = $this->trafficModel->getCitiesByCountry($country);
        
        return $this->response->setJSON($cities);
    }

    /**
     * Analisis sederhana untuk mendeteksi kemungkinan bot berdasarkan pola umum
     */
    public function analyzePotentialBot($ip, $country): bool
    {
        // Beberapa indikator bot (ini analisis sederhana, tidak 100% akurat)
        
        // 1. IP ranges yang umum digunakan cloud providers/hosting (tidak selalu bot, tapi sering)
        $cloudProviderRanges = [
            // AWS
            '54.', '52.', '3.', '13.', '18.', '34.', '35.', '36.',
            // Google Cloud
            '34.', '35.', '104.', '130.', '146.', '199.',
            // DigitalOcean
            '68.', '134.', '138.', '159.', '165.', '167.', '174.', '178.',
            // Azure
            '13.', '20.', '40.', '51.', '52.', '104.', '191.',
        ];
        
        foreach ($cloudProviderRanges as $range) {
            if (str_starts_with($ip, $range)) {
                return true;
            }
        }
        
        // 2. Pola akses cepat berurutan (akan diimplementasi nanti jika perlu)
        
        return false;
    }

    /**
     * Cleanup duplikasi data dan data admin (hanya untuk development)
     */
    public function cleanup()
    {
        $db = \Config\Database::connect();
        
        // 1. Hapus duplikasi berdasarkan IP dan waktu yang terlalu dekat (dalam 1 menit)
        $duplicateQuery = "
            DELETE t1 FROM traffic_logs t1
            INNER JOIN traffic_logs t2 
            WHERE 
                t1.id > t2.id
                AND t1.ip_address = t2.ip_address
                AND ABS(TIMESTAMPDIFF(SECOND, t1.created_at, t2.created_at)) < 60
        ";
        
        $duplicateResult = $db->query($duplicateQuery);
        $duplicatesRemoved = $db->affectedRows();
        
        // 2. Hapus data dari IP lokal/admin (127.0.0.1, ::1, dll)
        $adminQuery = "
            DELETE FROM traffic_logs 
            WHERE ip_address IN ('127.0.0.1', '::1', 'localhost')
            OR ip_address LIKE '192.168.%'
            OR ip_address LIKE '10.%'
            OR ip_address LIKE '172.16.%'
            OR ip_address LIKE '172.17.%'
            OR ip_address LIKE '172.18.%'
            OR ip_address LIKE '172.19.%'
            OR ip_address LIKE '172.20.%'
            OR ip_address LIKE '172.21.%'
            OR ip_address LIKE '172.22.%'
            OR ip_address LIKE '172.23.%'
            OR ip_address LIKE '172.24.%'
            OR ip_address LIKE '172.25.%'
            OR ip_address LIKE '172.26.%'
            OR ip_address LIKE '172.27.%'
            OR ip_address LIKE '172.28.%'
            OR ip_address LIKE '172.29.%'
            OR ip_address LIKE '172.30.%'
            OR ip_address LIKE '172.31.%'
        ";
        
        $adminResult = $db->query($adminQuery);
        $adminRecordsRemoved = $db->affectedRows();
        
        echo "<h2>Cleanup Complete!</h2>";
        echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>Results:</h4>";
        echo "<p>✅ Duplikasi dihapus: <strong>{$duplicatesRemoved}</strong> records</p>";
        echo "<p>✅ Data admin/local IP dihapus: <strong>{$adminRecordsRemoved}</strong> records</p>";
        echo "</div>";
        
        // Tampilkan statistik setelah cleanup
        $totalAfter = $db->query("SELECT COUNT(*) as total FROM traffic_logs")->getRow()->total;
        $todayAfter = $db->query("SELECT COUNT(*) as total FROM traffic_logs WHERE access_date = CURDATE()")->getRow()->total;
        
        echo "<div style='background: #cce5ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>Statistik Setelah Cleanup:</h4>";
        echo "<p>📊 Total records: <strong>" . number_format($totalAfter) . "</strong></p>";
        echo "<p>📊 Hari ini: <strong>" . number_format($todayAfter) . "</strong></p>";
        echo "</div>";
        
        echo "<p><a href='" . base_url('admin/traffic') . "' class='btn btn-primary' style='background: #007bff; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Kembali ke Traffic Analysis</a></p>";
        echo "<p><a href='" . base_url('admin/traffic/logs') . "' class='btn btn-info' style='background: #17a2b8; color: white; padding: 10px 15px; text-decoration: none; border-radius: 5px;'>Lihat Log Pengunjung</a></p>";
        
        return '';
    }

    /**
     * Generate pagination URLs untuk view
     * 
     * @param array $paginationData
     * @param array $filters
     * @return array
     */
    private function generatePaginationUrls(array $paginationData, array $filters): array
    {
        $baseUrl = base_url('admin/traffic/logs');
        $urls = [];
        
        // Previous URL
        if ($paginationData['has_prev']) {
            $params = array_merge($filters, ['page' => $paginationData['current_page'] - 1]);
            $urls['prev'] = $baseUrl . '?' . http_build_query($params);
        }
        
        // Next URL
        if ($paginationData['has_next']) {
            $params = array_merge($filters, ['page' => $paginationData['current_page'] + 1]);
            $urls['next'] = $baseUrl . '?' . http_build_query($params);
        }
        
        // Page URLs
        $startPage = max(1, $paginationData['current_page'] - 2);
        $endPage = min($paginationData['total_pages'], $paginationData['current_page'] + 2);
        
        // First page
        if ($startPage > 1) {
            $params = array_merge($filters, ['page' => 1]);
            $urls['first'] = $baseUrl . '?' . http_build_query($params);
        }
        
        // Last page
        if ($endPage < $paginationData['total_pages']) {
            $params = array_merge($filters, ['page' => $paginationData['total_pages']]);
            $urls['last'] = $baseUrl . '?' . http_build_query($params);
        }
        
        // Page range
        $urls['pages'] = [];
        for ($i = $startPage; $i <= $endPage; $i++) {
            $params = array_merge($filters, ['page' => $i]);
            $urls['pages'][$i] = $baseUrl . '?' . http_build_query($params);
        }
        
        return $urls;
    }
}
