<?php

namespace App\Models;

use CodeIgniter\Model;

class DiscountRuleModel extends Model
{
    protected $table = 'discount_rules';
    protected $primaryKey = 'discount_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    protected $allowedFields = [
        'name',
        'discount_type',
        'product_ids',       // JSON: {"PROD001": {"discounted_price": 1250000}, ...}
        'min_amount',
        'max_amount', 
        'discount_percentage',
        'is_active',
        'usage_limit',
        'usage_count',
        'start_date',
        'end_date',
        'valid_pickup_start_date',
        'valid_pickup_end_date',
        'valid_pickup_start_time',
        'valid_pickup_end_time',
        'created_at',
        'updated_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'min_amount' => 'permit_empty|numeric',
        'max_amount' => 'permit_empty|numeric',
        'discount_percentage' => 'permit_empty|numeric|less_than_equal_to[100]',
        'is_active' => 'required|in_list[0,1]'
    ];

    protected $validationMessages = [
        'min_amount' => [
            'numeric' => 'Minimal pembelian harus berupa angka'
        ],
        'max_amount' => [
            'numeric' => 'Maksimal pembelian harus berupa angka'
        ],
        'discount_percentage' => [
            'numeric' => 'Persentase diskon harus berupa angka',
            'less_than_equal_to' => 'Persentase diskon tidak boleh lebih dari 100%'
        ]
    ];

    /**
     * REVISI: Menerima parameter $tanggalCheck agar validasi dinamis mengikuti pilihan customer
     * @param array $discount
     * @param string|null $tanggalCheck
     * @param bool $strictTimeCheck
     * @return bool
     */
    public function isDiscountValid($discount, $tanggalCheck = null, $strictTimeCheck = false)
    {
        // Cek status aktif
        if ((int)($discount['is_active'] ?? 0) !== 1) {
            return false;
        }

        // Cek batas penggunaan
        if (!empty($discount['usage_limit']) && (int)$discount['usage_count'] >= (int)$discount['usage_limit']) {
            return false;
        }

        $now = date('Y-m-d H:i:s');
        
        // start_date & end_date = Masa aktif promo (diperiksa terhadap waktu order saat ini)
        if (!empty($discount['start_date']) && $now < $discount['start_date']) {
            return false;
        }
        if (!empty($discount['end_date']) && $now > $discount['end_date']) {
            return false;
        }

        // valid_pickup_start_date & valid_pickup_end_date = Masa berlaku tanggal pengantaran
        if ($strictTimeCheck) {
            if (empty($tanggalCheck)) {
                // Jika butuh tanggal pengantaran tapi user belum milih tanggal, diskon tidak berlaku
                if (!empty($discount['valid_pickup_start_date']) || !empty($discount['valid_pickup_end_date'])) {
                    return false;
                }
            } else {
                // Normalisasi format string tanggal/jam pengantaran agar seragam standar SQL
                $cleanDate = str_replace('T', ' ', $tanggalCheck);
                if (strpos($cleanDate, '/') !== false) {
                    $parts = explode(' ', $cleanDate);
                    $dateParts = explode('/', $parts[0]);
                    $timePart = $parts[1] ?? '00:00:00';
                    if (count($dateParts) === 3) {
                        $cleanDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0] . ' ' . $timePart;
                    }
                }
                $deliveryTimestamp = strtotime($cleanDate);
                
                if ($deliveryTimestamp !== false) {
                    $dateCheck = date('Y-m-d', $deliveryTimestamp);
                    
                    if (!empty($discount['valid_pickup_start_date']) && $dateCheck < $discount['valid_pickup_start_date']) {
                        return false;
                    }
                    if (!empty($discount['valid_pickup_end_date']) && $dateCheck > $discount['valid_pickup_end_date']) {
                        return false;
                    }

                    // Cek waktu pengambilan/pengantaran
                    if (!empty($discount['valid_pickup_start_time']) && !empty($discount['valid_pickup_end_time'])) {
                        $timeCheck = date('H:i:s', $deliveryTimestamp);
                        if ($timeCheck < $discount['valid_pickup_start_time'] || $timeCheck > $discount['valid_pickup_end_time']) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * REVISI: Menambahkan parameter $tanggalPengantaran
     * @param float $subtotal
     * @param string|null $tanggalPengantaran
     * @return array|null
     */
    public function getApplicableDiscount($subtotal, $tanggalPengantaran = null)
    {
        $query = $this->where('is_active', 1)
                      ->groupStart()
                          ->where('discount_type', 'subtotal')
                          ->orWhere('discount_type IS NULL')
                      ->groupEnd()
                      ->where('min_amount <=', $subtotal);
        
        $query->groupStart()
              ->where('max_amount >=', $subtotal)
              ->orWhere('max_amount IS NULL')
              ->orWhere('max_amount', 0)
              ->groupEnd();
        
        $discounts = $query->orderBy('discount_percentage', 'DESC')
                          ->findAll();
        
        foreach ($discounts as $discount) {
            // Lewatkan variabel tanggal pengantaran ke fungsi validasi
            if ($this->isDiscountValid($discount, $tanggalPengantaran, true)) {
                return $discount;
            }
        }
        
        return null;
    }

    /**
     * REVISI: Menambahkan parameter $tanggalPengantaran
     * @param string $productId
     * @param float $originalPrice
     * @param string|null $tanggalPengantaran
     * @return array|null
     */
    public function getProductDiscount($productId, $originalPrice = 0, $tanggalPengantaran = null)
    {
        $discounts = $this->where('is_active', 1)
                          ->where('discount_type', 'product')
                          ->findAll();
        
        foreach ($discounts as $discount) {
            // Lewatkan variabel tanggal pengantaran ke fungsi validasi
            if (!$this->isDiscountValid($discount, $tanggalPengantaran, true)) {
                continue;
            }

            $productData = json_decode($discount['product_ids'] ?? '[]', true);
            
            if (is_array($productData) && isset($productData[$productId])) {
                $discountInfo = $productData[$productId];
                $discountedPrice = (float)($discountInfo['discounted_price'] ?? 0);
                
                $discountPercentage = 0;
                $discountAmount = 0;
                if ($originalPrice > 0 && $discountedPrice > 0) {
                    $discountAmount = $originalPrice - $discountedPrice;
                    $discountPercentage = round(($discountAmount / $originalPrice) * 100, 2);
                }
                
                return [
                    'discount_id' => $discount['discount_id'],
                    'discount_name' => $discount['name'] ?? 'Diskon Produk',
                    'original_price' => $originalPrice,
                    'discounted_price' => $discountedPrice,
                    'discount_amount' => $discountAmount,
                    'discount_percentage' => $discountPercentage,
                    'usage_limit' => $discount['usage_limit'],
                    'usage_count' => $discount['usage_count'],
                    'start_date' => $discount['start_date'],
                    'end_date' => $discount['end_date'],
                    'valid_pickup_start_date' => $discount['valid_pickup_start_date'] ?? null,
                    'valid_pickup_end_date' => $discount['valid_pickup_end_date'] ?? null,
                    'valid_pickup_start_time' => $discount['valid_pickup_start_time'],
                    'valid_pickup_end_time' => $discount['valid_pickup_end_time'],
                ];
            }
            
            if (is_array($productData) && !isset($productData[$productId]) && in_array($productId, $productData)) {
                $discountPercentage = (float)($discount['discount_percentage'] ?? 0);
                $discountAmount = $originalPrice * ($discountPercentage / 100);
                $discountedPrice = $originalPrice - $discountAmount;
                
                return [
                    'discount_id' => $discount['discount_id'],
                    'discount_name' => $discount['name'] ?? 'Diskon Produk',
                    'original_price' => $originalPrice,
                    'discounted_price' => $discountedPrice,
                    'discount_amount' => $discountAmount,
                    'discount_percentage' => $discountPercentage,
                    'usage_limit' => $discount['usage_limit'],
                    'usage_count' => $discount['usage_count'],
                    'start_date' => $discount['start_date'],
                    'end_date' => $discount['end_date'],
                    'valid_pickup_start_date' => $discount['valid_pickup_start_date'] ?? null,
                    'valid_pickup_end_date' => $discount['valid_pickup_end_date'] ?? null,
                    'valid_pickup_start_time' => $discount['valid_pickup_start_time'],
                    'valid_pickup_end_time' => $discount['valid_pickup_end_time'],
                ];
            }
        }
        
        return null;
    }

    public function getProductsWithDiscounts()
    {
        $discounts = $this->where('is_active', 1)
                          ->where('discount_type', 'product')
                          ->findAll();
        
        $result = [];
        
        foreach ($discounts as $discount) {
            if (!$this->isDiscountValid($discount)) {
                continue;
            }
            
            $productData = json_decode($discount['product_ids'] ?? '[]', true);
            
            if (is_array($productData)) {
                foreach ($productData as $productId => $info) {
                    if (is_array($info) && isset($info['discounted_price'])) {
                        $result[$productId] = [
                            'discount_id' => $discount['discount_id'],
                            'discount_name' => $discount['name'] ?? 'Diskon Produk',
                            'discounted_price' => (float)$info['discounted_price'],
                            'discount_percentage' => (float)($discount['discount_percentage'] ?? 0),
                            'end_date' => $discount['end_date'],
                            'valid_pickup_start_date' => $discount['valid_pickup_start_date'] ?? null,
                            'valid_pickup_end_date' => $discount['valid_pickup_end_date'] ?? null,
                        ];
                    }
                }
            }
        }
        
        return $result;
    }

    public function getActiveProductDiscounts()
    {
        $discounts = $this->where('is_active', 1)
                          ->where('discount_type', 'product')
                          ->findAll();
        
        return array_filter($discounts, fn($d) => $this->isDiscountValid($d));
    }

    public function getActiveDiscounts()
    {
        $discounts = $this->where('is_active', 1)
                          ->orderBy('min_amount', 'ASC')
                          ->findAll();
        
        return array_filter($discounts, fn($d) => $this->isDiscountValid($d));
    }

    public function incrementUsage($discountId)
    {
        $discount = $this->find($discountId);
        if (!$discount) {
            return false;
        }

        $newCount = ((int)($discount['usage_count'] ?? 0)) + 1;
        $updateData = ['usage_count' => $newCount];

        if (!empty($discount['usage_limit']) && $newCount >= (int)$discount['usage_limit']) {
            $updateData['is_active'] = 0;
        }

        return $this->update($discountId, $updateData);
    }

    public function resetUsage($discountId)
    {
        return $this->update($discountId, ['usage_count' => 0, 'is_active' => 1]);
    }

    public function getRemainingUsage($discount)
    {
        if (empty($discount['usage_limit'])) {
            return null;
        }
        return max(0, (int)$discount['usage_limit'] - (int)($discount['usage_count'] ?? 0));
    }
}
