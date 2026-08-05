<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * Model untuk mengelola data traffic logs (kunjungan pengunjung)
 */
class TrafficLogModel extends Model
{
    protected $table            = 'traffic_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = ['ip_address', 'country', 'city', 'access_date', 'access_time', 'page_url', 'page_title', 'user_agent', 'referer'];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $deletedField  = 'deleted_at';

    // Validation
    protected $validationRules      = [];
    protected $validationMessages   = [];
    protected $skipValidation       = false;
    protected $cleanValidationRules = true;

    // Callbacks
    protected $allowCallbacks = true;
    protected $beforeInsert   = [];
    protected $afterInsert    = [];
    protected $beforeUpdate   = [];
    protected $afterUpdate    = [];
    protected $beforeFind     = [];
    protected $afterFind      = [];
    protected $beforeDelete   = [];
    protected $afterDelete    = [];

    /**
     * Mencatat kunjungan pengunjung dengan mendapatkan data geolokasi dan page info
     * 
     * @param string $ip IP address pengunjung
     * @param string $userAgent User agent untuk analisis bot
     * @param string $pageUrl URL halaman yang diakses
     * @param string $pageTitle Title halaman
     * @param string $referer Halaman asal
     * @return bool
     */
    public function logVisitor(string $ip, string $userAgent = '', string $pageUrl = '', string $pageTitle = '', string $referer = ''): bool
    {
        try {
            // Double check: cek lagi apakah IP ini sudah dicatat dalam 5 menit terakhir
            $recentVisit = $this->where('ip_address', $ip)
                               ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-5 minutes')))
                               ->first();
            
            if ($recentVisit) {
                return false; // Skip jika sudah ada kunjungan baru-baru ini
            }

            // Cek apakah IP adalah lokal/private
            if ($this->isPrivateIP($ip)) {
                // Untuk IP lokal, simpan data tanpa geolokasi
                $data = [
                    'ip_address'  => $ip,
                    'country'     => 'Local',
                    'city'        => 'Local',
                    'access_date' => date('Y-m-d'),
                    'access_time' => date('H:i:s'),
                    'page_url'    => $pageUrl,
                    'page_title'  => $pageTitle,
                    'user_agent'  => $userAgent,
                    'referer'     => $referer,
                ];
            } else {
                // Dapatkan data geolokasi dari IP-API
                $geoData = $this->getGeolocationData($ip);
                
                $data = [
                    'ip_address'  => $ip,
                    'country'     => $geoData['country'] ?? 'Unknown',
                    'city'        => $geoData['city'] ?? 'Unknown',
                    'access_date' => date('Y-m-d'),
                    'access_time' => date('H:i:s'),
                    'page_url'    => $pageUrl,
                    'page_title'  => $pageTitle,
                    'user_agent'  => $userAgent,
                    'referer'     => $referer,
                ];
            }

            // Simpan data ke database
            return $this->insert($data);
            
        } catch (\Exception $e) {
            // Log error jika diperlukan
            log_message('error', 'Failed to log visitor: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Mengecek apakah IP adalah private/local IP
     * 
     * @param string $ip
     * @return bool
     */
    private function isPrivateIP(string $ip): bool
    {
        // Daftar IP lokal/private
        $privateRanges = [
            '127.0.0.1',
            '::1',
            '10.0.0.0/8',
            '172.16.0.0/12',
            '192.168.0.0/16',
            'localhost'
        ];

        // Cek apakah IP adalah localhost
        if (in_array($ip, ['127.0.0.1', '::1', 'localhost'])) {
            return true;
        }

        // Cek private IP ranges
        return filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false;
    }

    /**
     * Mendapatkan data geolokasi dari IP-API
     * 
     * @param string $ip
     * @return array
     */
    private function getGeolocationData(string $ip): array
    {
        try {
            $url = "http://ip-api.com/json/{$ip}";
            
            // Menggunakan curl untuk mendapatkan data
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200 && $response) {
                $data = json_decode($response, true);
                
                if ($data && $data['status'] === 'success') {
                    return [
                        'country' => $data['country'] ?? 'Unknown',
                        'city'    => $data['city'] ?? 'Unknown',
                    ];
                }
            }
            
        } catch (\Exception $e) {
            log_message('error', 'Geolocation API error: ' . $e->getMessage());
        }

        return ['country' => 'Unknown', 'city' => 'Unknown'];
    }

    /**
     * Mendapatkan data traffic per jam untuk 24 jam terakhir (exclude admin/local IPs)
     * 
     * @return array
     */
    public function getHourlyTraffic(): array
    {
        $builder = $this->db->table($this->table);
        
        return $builder->select("DATE_FORMAT(CONCAT(access_date, ' ', access_time), '%H') as hour, COUNT(*) as visits")
                      ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-24 hours')))
                      ->where('country !=', 'Local')
                      ->groupBy('hour')
                      ->orderBy('hour', 'ASC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Mendapatkan data traffic per hari untuk 30 hari terakhir (exclude admin/local IPs)
     * 
     * @return array
     */
    public function getDailyTraffic(): array
    {
        $builder = $this->db->table($this->table);
        
        return $builder->select('access_date as date, COUNT(*) as visits')
                      ->where('access_date >=', date('Y-m-d', strtotime('-30 days')))
                      ->where('country !=', 'Local')
                      ->groupBy('access_date')
                      ->orderBy('access_date', 'ASC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Mendapatkan negara teratas berdasarkan jumlah kunjungan
     * 
     * @param int $limit
     * @return array
     */
    public function getTopCountries(int $limit = 10): array
    {
        $builder = $this->db->table($this->table);
        
        return $builder->select('country, COUNT(*) as visits')
                      ->where('country !=', 'Unknown')
                      ->where('country !=', 'Local')
                      ->groupBy('country')
                      ->orderBy('visits', 'DESC')
                      ->limit($limit)
                      ->get()
                      ->getResultArray();
    }

    /**
     * Mendapatkan total kunjungan hari ini (exclude admin/local IPs)
     * 
     * @return int
     */
    public function getTodayVisits(): int
    {
        return $this->where('access_date', date('Y-m-d'))
                   ->where('country !=', 'Local')
                   ->countAllResults();
    }

    /**
     * Mendapatkan total kunjungan kemarin (exclude admin/local IPs)
     * 
     * @return int
     */
    public function getYesterdayVisits(): int
    {
        return $this->where('access_date', date('Y-m-d', strtotime('-1 day')))
                   ->where('country !=', 'Local')
                   ->countAllResults();
    }

    /**
     * Mendapatkan total kunjungan bulan ini (exclude admin/local IPs)
     * 
     * @return int
     */
    public function getMonthlyVisits(): int
    {
        return $this->where('access_date >=', date('Y-m-01'))
                   ->where('country !=', 'Local')
                   ->countAllResults();
    }

    /**
     * Mendapatkan kunjungan unik per hari (berdasarkan IP)
     * 
     * @return int
     */
    public function getUniqueVisitsToday(): int
    {
        return $this->distinct()
                   ->select('ip_address')
                   ->where('access_date', date('Y-m-d'))
                   ->countAllResults();
    }

    /**
     * Mendapatkan top 5 negara dengan visitor terbanyak hari ini
     * 
     * @return array
     */
    public function getTodayTopCountries(): array
    {
        $builder = $this->db->table($this->table);
        
        return $builder->select('country, COUNT(DISTINCT ip_address) as unique_visitors, COUNT(*) as total_visits')
                      ->where('access_date', date('Y-m-d'))
                      ->where('country !=', 'Unknown')
                      ->where('country !=', 'Local')
                      ->groupBy('country')
                      ->orderBy('unique_visitors', 'DESC')
                      ->limit(5)
                      ->get()
                      ->getResultArray();
    }

    /**
     * Mendapatkan statistik growth visitor
     * 
     * @return array
     */
    public function getGrowthStats(): array
    {
        $today = $this->getTodayVisits();
        $yesterday = $this->getYesterdayVisits();
        $lastWeek = $this->where('access_date >=', date('Y-m-d', strtotime('-7 days')))->countAllResults();
        
        $growthToday = $yesterday > 0 ? (($today - $yesterday) / $yesterday) * 100 : 0;
        
        return [
            'today' => $today,
            'yesterday' => $yesterday,
            'week' => $lastWeek,
            'growth_percent' => round($growthToday, 1),
        ];
    }

    /**
     * Mendapatkan data pengunjung dengan pagination dan filter
     * 
     * @param int $page
     * @param int $perPage
     * @param array $filters
     * @return array
     */
    public function getVisitorsPaginated(int $page = 1, int $perPage = 25, array $filters = []): array
    {
        $builder = $this->db->table($this->table);
        
        // Terapkan filter
        if (!empty($filters['country'])) {
            $builder->where('country', $filters['country']);
        }
        
        if (!empty($filters['city'])) {
            $builder->where('city', $filters['city']);
        }
        
        if (!empty($filters['date_from'])) {
            $builder->where('access_date >=', $filters['date_from']);
        }
        
        if (!empty($filters['date_to'])) {
            $builder->where('access_date <=', $filters['date_to']);
        }
        
        if (!empty($filters['ip'])) {
            $builder->like('ip_address', $filters['ip']);
        }
        
        // Hitung total records
        $totalRecords = $builder->countAllResults(false); // false = tidak reset query
        
        // Pagination
        $offset = ($page - 1) * $perPage;
        $data = $builder->orderBy('created_at', 'DESC')
                       ->limit($perPage, $offset)
                       ->get()
                       ->getResultArray();
        
        return [
            'data' => $data,
            'total' => $totalRecords,
            'per_page' => $perPage,
            'current_page' => $page,
            'total_pages' => ceil($totalRecords / $perPage),
            'has_next' => $page < ceil($totalRecords / $perPage),
            'has_prev' => $page > 1,
        ];
    }

    /**
     * Mendapatkan daftar negara yang ada dalam database
     * 
     * @return array
     */
    public function getAvailableCountries(): array
    {
        $builder = $this->db->table($this->table);
        
        return $builder->select('country, COUNT(*) as count')
                      ->where('country !=', '')
                      ->where('country IS NOT NULL')
                      ->groupBy('country')
                      ->orderBy('count', 'DESC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Mendapatkan daftar kota berdasarkan negara
     * 
     * @param string $country
     * @return array
     */
    public function getCitiesByCountry(string $country): array
    {
        $builder = $this->db->table($this->table);
        
        return $builder->select('city, COUNT(*) as count')
                      ->where('country', $country)
                      ->where('city !=', '')
                      ->where('city IS NOT NULL')
                      ->groupBy('city')
                      ->orderBy('count', 'DESC')
                      ->get()
                      ->getResultArray();
    }

    /**
     * Analisis sederhana untuk mendeteksi kemungkinan bot
     * 
     * @param string $ip
     * @param string $country
     * @return bool
     */
    public function analyzePotentialBot(string $ip, string $country): bool
    {
        // IP ranges yang umum digunakan cloud providers/hosting
        $cloudProviderRanges = [
            // AWS
            '54.', '52.', '3.', '13.', '18.', '34.', '35.', '36.',
            // Google Cloud
            '34.', '35.', '104.', '130.', '146.', '199.',
            // DigitalOcean
            '68.', '134.', '138.', '159.', '165.', '167.', '174.', '178.',
            // Azure
            '13.', '20.', '40.', '51.', '52.', '104.', '191.',
            // Linode
            '139.', '172.', '173.', '176.', '192.',
            // Vultr
            '45.', '149.', '207.',
        ];
        
        foreach ($cloudProviderRanges as $range) {
            if (str_starts_with($ip, $range)) {
                return true;
            }
        }
        
        return false;
    }

    /**
     * Mendapatkan halaman paling populer
     * 
     * @param int $limit
     * @param int $days
     * @return array
     */
    public function getPopularPages(int $limit = 10, int $days = 30): array
    {
        $builder = $this->db->table($this->table);
        
        // Cek apakah ada data dengan page_url yang tidak kosong
        $hasPageData = $builder->where('page_url IS NOT NULL')
                              ->where('page_url !=', '')
                              ->countAllResults();
        
        if ($hasPageData == 0) {
            return []; // Return empty array jika tidak ada data page_url
        }
        
        $builder = $this->db->table($this->table);
        
        return $builder->select('page_url, page_title, COUNT(*) as visits, COUNT(DISTINCT ip_address) as unique_visitors')
                      ->where('access_date >=', date('Y-m-d', strtotime("-{$days} days")))
                      ->where('country !=', 'Local')
                      ->where('page_url IS NOT NULL')
                      ->where('page_url !=', '')
                      ->groupBy(['page_url', 'page_title'])
                      ->orderBy('visits', 'DESC')
                      ->limit($limit)
                      ->get()
                      ->getResultArray();
    }

    /**
     * Mendapatkan halaman entry (landing pages)
     * 
     * @param int $limit
     * @param int $days
     * @return array
     */
    public function getEntryPages(int $limit = 10, int $days = 30): array
    {
        $builder = $this->db->table($this->table);
        
        // Cek apakah ada data dengan page_url yang tidak kosong
        $hasPageData = $builder->where('page_url IS NOT NULL')
                              ->where('page_url !=', '')
                              ->countAllResults();
        
        if ($hasPageData == 0) {
            return []; // Return empty array jika tidak ada data page_url
        }
        
        $builder = $this->db->table($this->table);
        
        // Mendapatkan domain saat ini
        $currentDomain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        return $builder->select('page_url, page_title, COUNT(*) as entries')
                      ->where('access_date >=', date('Y-m-d', strtotime("-{$days} days")))
                      ->where('country !=', 'Local')
                      ->where('page_url IS NOT NULL')
                      ->where('page_url !=', '')
                      ->groupStart()
                          ->where('referer IS NULL')
                          ->orWhere('referer', '')
                          ->orWhere('referer NOT LIKE', "%{$currentDomain}%")
                      ->groupEnd()
                      ->groupBy(['page_url', 'page_title'])
                      ->orderBy('entries', 'DESC')
                      ->limit($limit)
                      ->get()
                      ->getResultArray();
    }

    /**
     * Mendapatkan sumber traffic (referer analysis)
     * 
     * @param int $limit
     * @param int $days
     * @return array
     */
    public function getTrafficSources(int $limit = 10, int $days = 30): array
    {
        try {
            // Ambil semua referer dan proses di PHP untuk menghindari error SQL kompleks
            $referers = $this->db->table($this->table)
                               ->select('referer')
                               ->where('access_date >=', date('Y-m-d', strtotime("-{$days} days")))
                               ->where('country !=', 'Local')
                               ->get()
                               ->getResultArray();
            
            $sourceStats = [];
            
            foreach ($referers as $row) {
                $referer = $row['referer'] ?? '';
                $source = 'Direct';
                
                if (!empty($referer)) {
                    $referer = strtolower($referer);
                    
                    if (strpos($referer, 'google.') !== false) {
                        $source = 'Google';
                    } elseif (strpos($referer, 'facebook.') !== false) {
                        $source = 'Facebook';
                    } elseif (strpos($referer, 'instagram.') !== false) {
                        $source = 'Instagram';
                    } elseif (strpos($referer, 'twitter.') !== false || strpos($referer, 't.co') !== false) {
                        $source = 'Twitter';
                    } elseif (strpos($referer, 'youtube.') !== false) {
                        $source = 'YouTube';
                    } elseif (strpos($referer, 'linkedin.') !== false) {
                        $source = 'LinkedIn';
                    } elseif (strpos($referer, 'tiktok.') !== false) {
                        $source = 'TikTok';
                    } else {
                        // Extract domain
                        $referer = str_replace(['https://', 'http://'], '', $referer);
                        $domain = explode('/', $referer)[0];
                        $domain = explode('?', $domain)[0];
                        $source = $domain ?: 'Other';
                    }
                }
                
                if (!isset($sourceStats[$source])) {
                    $sourceStats[$source] = 0;
                }
                $sourceStats[$source]++;
            }
            
            // Convert to required format and sort
            $result = [];
            foreach ($sourceStats as $source => $visits) {
                $result[] = ['referer_domain' => $source, 'visits' => $visits];
            }
            
            // Sort by visits descending
            usort($result, function($a, $b) {
                return $b['visits'] - $a['visits'];
            });
            
            return array_slice($result, 0, $limit);
            
        } catch (Exception $e) {
            // Jika ada error, return array kosong
            return [];
        }
    }

    /**
     * Mendapatkan browser analysis
     * 
     * @param int $limit
     * @return array
     */
    public function getBrowserStats(int $limit = 10): array
    {
        // Cek apakah tabel memiliki data
        $totalRows = $this->db->table($this->table)->countAllResults();
        if ($totalRows == 0) {
            return [];
        }
        
        // Cek apakah kolom user_agent ada dan memiliki data
        try {
            $hasUserAgent = $this->db->table($this->table)
                                   ->where('user_agent IS NOT NULL')
                                   ->where('user_agent !=', '')
                                   ->countAllResults();
            
            if ($hasUserAgent == 0) {
                return [
                    ['browser' => 'Unknown', 'count' => $totalRows]
                ];
            }
            
            // Ambil data user agent dan proses di PHP
            $userAgents = $this->db->table($this->table)
                                 ->select('user_agent')
                                 ->where('access_date >=', date('Y-m-d', strtotime('-30 days')))
                                 ->where('country !=', 'Local')
                                 ->where('user_agent IS NOT NULL')
                                 ->where('user_agent !=', '')
                                 ->get()
                                 ->getResultArray();
            
            $browserStats = [];
            
            foreach ($userAgents as $row) {
                $userAgent = strtolower($row['user_agent']);
                $browser = 'Other';
                
                if (strpos($userAgent, 'chrome') !== false && strpos($userAgent, 'edg') === false) {
                    $browser = 'Chrome';
                } elseif (strpos($userAgent, 'firefox') !== false) {
                    $browser = 'Firefox';
                } elseif (strpos($userAgent, 'safari') !== false && strpos($userAgent, 'chrome') === false) {
                    $browser = 'Safari';
                } elseif (strpos($userAgent, 'edg') !== false) {
                    $browser = 'Edge';
                } elseif (strpos($userAgent, 'opera') !== false) {
                    $browser = 'Opera';
                } elseif (strpos($userAgent, 'bot') !== false || strpos($userAgent, 'crawler') !== false) {
                    $browser = 'Bot/Crawler';
                }
                
                if (!isset($browserStats[$browser])) {
                    $browserStats[$browser] = 0;
                }
                $browserStats[$browser]++;
            }
            
            // Convert to required format and sort
            $result = [];
            foreach ($browserStats as $browser => $count) {
                $result[] = ['browser' => $browser, 'count' => $count];
            }
            
            // Sort by count descending
            usort($result, function($a, $b) {
                return $b['count'] - $a['count'];
            });
            
            return array_slice($result, 0, $limit);
            
        } catch (Exception $e) {
            // Jika ada error, return data sederhana
            return [
                ['browser' => 'Unknown', 'count' => $totalRows]
            ];
        }
    }

    /**
     * Mendapatkan path analysis untuk melihat flow pengunjung
     * 
     * @param string $specificPage
     * @return array
     */
    public function getPageFlow(string $specificPage = ''): array
    {
        $builder = $this->db->table($this->table);
        
        if ($specificPage) {
            return $builder->select('page_url, COUNT(*) as visits')
                          ->where('ip_address IN (SELECT DISTINCT ip_address FROM traffic_logs WHERE page_url LIKE "%' . $specificPage . '%")')
                          ->where('country !=', 'Local')
                          ->where('page_url IS NOT NULL')
                          ->groupBy('page_url')
                          ->orderBy('visits', 'DESC')
                          ->limit(20)
                          ->get()
                          ->getResultArray();
        }
        
        return [];
    }
}
