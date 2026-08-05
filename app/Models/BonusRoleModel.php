<?php

namespace App\Models;

use CodeIgniter\Model;

class BonusRoleModel extends Model
{
    protected $table = 'bonus_rules';
    protected $primaryKey = 'bonus_id';
    protected $allowedFields = [
        'rule_name', 
        'bonus_item_name', 
        'applicable_product_ids', 
        'quota_limit', 
        'usage_count', 
        'start_date', 
        'end_date', 
        'bonus_config', 
        'is_active'
    ];

    /**
     * Fungsi untuk mengecek apakah keranjang mendapatkan bonus
     */
    public function getApplicableBonuses(array $cartItems)
    {
        $now = date('Y-m-d H:i:s');
        
        // 1. Cari promo yang sedang aktif secara waktu, status, dan kuota
        $rule = $this->where('is_active', 1)
                     ->where('usage_count < quota_limit')
                     ->where('start_date <=', $now)
                     ->where('end_date >=', $now)
                     ->first();

        if (!$rule) {
            return [];
        }

        // 2. Siapkan data produk yang ikut promo dan aturan harganya
        $allowedIds = explode(',', str_replace(' ', '', $rule['applicable_product_ids']));
        $tiering = json_decode($rule['bonus_config'], true);
        
        // Urutkan kunci (harga) dari yang paling mahal ke termurah agar tiering tepat
        krsort($tiering); 

        $appliedBonuses = [];

        foreach ($cartItems as $item) {
            $productId = (string)($item['id'] ?? $item['product_id'] ?? '');
            
            // 3. Cek apakah produk ini masuk dalam daftar promo
            if (in_array($productId, $allowedIds)) {
                $itemPrice = (float)($item['price'] ?? 0);
                $qtyBonus = 0;

                // 4. Hitung jumlah bonus berdasarkan harga item (Tiering)
                foreach ($tiering as $minPrice => $amount) {
                    if ($itemPrice >= (float)$minPrice) {
                        $qtyBonus = $amount;
                        break; // Berhenti di tier harga tertinggi yang terpenuhi
                    }
                }

                if ($qtyBonus > 0) {
                    $appliedBonuses[] = [
                        'promo_id'   => $rule['bonus_id'],
                        'promo_name' => $rule['rule_name'],
                        'bonus_item' => $qtyBonus . " pcs " . $rule['bonus_item_name'],
                        'for_product'=> $item['name'] ?? ($item['nama_produk'] ?? 'Produk')
                    ];
                }
            }
        }

        return $appliedBonuses;
    }
}