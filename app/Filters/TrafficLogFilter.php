<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\TrafficLogModel;

/**
 * Filter untuk mencatat setiap kunjungan ke website
 */
class TrafficLogFilter implements FilterInterface
{
    /**
     * Filter yang dijalankan sebelum controller
     * Mencatat setiap kunjungan pengunjung
     * 
     * @param RequestInterface $request
     * @param array|null $arguments
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        try {
            // Skip jika user adalah admin/management (sudah login)
            $session = session();
            if ($session->get('isLoggedIn') || $session->get('role')) {
                return; // Skip tracking untuk user yang sudah login sebagai admin
            }

            // Dapatkan IP address pengunjung
            $ipAddress = $request->getIPAddress();
            
            // Skip pencatatan untuk request AJAX, API, atau file assets
            if ($this->shouldSkipLogging($request)) {
                return;
            }

            // Cek apakah IP ini sudah dicatat dalam 5 menit terakhir untuk menghindari duplikasi
            if ($this->isRecentlyLogged($ipAddress)) {
                return;
            }

            // Load model traffic log
            $trafficModel = new TrafficLogModel();
            
            // Dapatkan informasi halaman dan browser
            $userAgent = $request->getUserAgent()->getAgentString() ?? '';
            $uri = $request->getUri();
            $pageUrl = $uri->getPath(); // Hanya ambil path saja
            $referer = $request->getHeaderLine('Referer') ?? '';
            
            // Generate page title berdasarkan URL path
            $pageTitle = $this->generatePageTitle($pageUrl);
            
            // Debug log untuk melihat nilai yang dikirim
            log_message('info', 'TrafficLog Debug - URL: ' . $pageUrl . ', Title: ' . $pageTitle . ', Path: ' . $request->getUri()->getPath());
            
            // Catat kunjungan pengunjung dengan informasi halaman
            $trafficModel->logVisitor($ipAddress, $userAgent, $pageUrl, $pageTitle, $referer);
            
        } catch (\Exception $e) {
            // Log error tapi tidak mengganggu request normal
            log_message('error', 'TrafficLogFilter error: ' . $e->getMessage());
        }
    }

    /**
     * Filter yang dijalankan setelah controller
     * 
     * @param RequestInterface $request
     * @param ResponseInterface $response
     * @param array|null $arguments
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi khusus setelah response
    }

    /**
     * Menentukan apakah logging harus dilewati untuk request tertentu
     * 
     * @param RequestInterface $request
     * @return bool
     */
    private function shouldSkipLogging(RequestInterface $request): bool
    {
        $uri = $request->getUri();
        $path = $uri->getPath();
        
        // Skip untuk file assets (CSS, JS, images, dll)
        $assetExtensions = ['.css', '.js', '.png', '.jpg', '.jpeg', '.gif', '.ico', '.svg', '.woff', '.woff2', '.ttf', '.map', '.webp'];
        foreach ($assetExtensions as $ext) {
            if (str_ends_with(strtolower($path), $ext)) {
                return true;
            }
        }

        // Skip untuk request AJAX
        if ($request->isAJAX()) {
            return true;
        }

        // Skip untuk API endpoints
        if (str_starts_with($path, '/api/')) {
            return true;
        }

        // Skip untuk semua halaman admin (management tidak perlu ditrack)
        if (str_starts_with($path, '/admin/') || str_contains($path, '/admin')) {
            return true;
        }

        // Skip untuk admin API calls
        if (str_contains($path, '/api-data') || str_contains($path, '/debug')) {
            return true;
        }

        // Skip untuk polling endpoints
        if (str_contains($path, 'check-new-orders') || str_contains($path, 'polling')) {
            return true;
        }

        // Skip untuk robots.txt, sitemap.xml, dll
        $skipPaths = ['/robots.txt', '/sitemap.xml', '/favicon.ico'];
        if (in_array($path, $skipPaths)) {
            return true;
        }

        // Skip untuk path yang mengandung folder assets
        if (str_contains($path, '/assets/') || str_contains($path, '/uploads/')) {
            return true;
        }

        return false;
    }

    /**
     * Mengecek apakah IP sudah dicatat dalam waktu dekat untuk menghindari duplikasi
     * 
     * @param string $ip
     * @return bool
     */
    private function isRecentlyLogged(string $ip): bool
    {
        try {
            $trafficModel = new TrafficLogModel();
            
            // Cek apakah IP ini sudah dicatat dalam 5 menit terakhir
            $recentVisit = $trafficModel->where('ip_address', $ip)
                                       ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-5 minutes')))
                                       ->first();
            
            return $recentVisit !== null;
            
        } catch (\Exception $e) {
            log_message('error', 'Error checking recent visit: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate page title berdasarkan URL path
     * 
     * @param string $path
     * @return string
     */
    private function generatePageTitle(string $path): string
    {
        // Mapping URL ke title yang lebih readable
        $pathTitles = [
            '/' => 'Homepage',
            '/shop' => 'Shop - Katalog Produk',
            '/artikel' => 'Artikel & Blog',
            '/tracking' => 'Lacak Pesanan',
            '/checkout' => 'Checkout',
            '/cart' => 'Keranjang Belanja',
        ];

        // Cek exact match terlebih dahulu
        if (isset($pathTitles[$path])) {
            return $pathTitles[$path];
        }

        // Pattern matching untuk URL dinamis
        if (str_starts_with($path, '/shop/product/')) {
            return 'Detail Produk - ' . ucfirst(str_replace(['/shop/product/', '-', '_'], ['', ' ', ' '], $path));
        }

        if (str_starts_with($path, '/shop/category/')) {
            return 'Kategori - ' . ucfirst(str_replace(['/shop/category/', '-', '_'], ['', ' ', ' '], $path));
        }

        if (str_starts_with($path, '/artikel/')) {
            return 'Artikel - ' . ucfirst(str_replace(['/artikel/', '-', '_'], ['', ' ', ' '], $path));
        }

        if (str_starts_with($path, '/checkout/')) {
            return 'Checkout - ' . ucfirst(str_replace('/', ' ', $path));
        }

        if (str_starts_with($path, '/tracking')) {
            return 'Tracking Pesanan';
        }

        if (str_starts_with($path, '/payment/')) {
            return 'Pembayaran';
        }

        if (str_starts_with($path, '/order-success/')) {
            return 'Pesanan Berhasil';
        }

        // Tambahan untuk halaman umum
        if (preg_match('/\/shop\/.*/', $path)) {
            return 'Shop - ' . ucwords(str_replace(['/', '-', '_'], [' ', ' ', ' '], $path));
        }

        if (preg_match('/\/custom-order/', $path)) {
            return 'Custom Order';
        }

        if (preg_match('/\/contact/', $path)) {
            return 'Kontak Kami';
        }

        if (preg_match('/\/about/', $path)) {
            return 'Tentang Kami';
        }

        // Default: capitalize dan clean path
        $title = str_replace(['/', '-', '_'], [' ', ' ', ' '], $path);
        $title = ucwords(trim($title));
        
        // Jika masih kosong, gunakan path asli
        if (empty($title)) {
            $title = $path ?: 'Homepage';
        }
        
        return $title;
    }
}
