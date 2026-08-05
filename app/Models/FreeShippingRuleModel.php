<?php

namespace App\Models;

use CodeIgniter\Model;

class FreeShippingRuleModel extends Model
{
    protected $table = 'free_shipping_rules';
    protected $primaryKey = 'rule_id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $protectFields = true;
    
    protected $allowedFields = [
        'min_amount',
        'max_amount',
        'max_distance_km',
        'is_active',
        'created_at',
        'updated_at', // Tadi kurang koma di sini
        'apply_to_all',
        'product_ids',
        'start_date',
        'end_date'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    protected $validationRules = [
        'min_amount'      => 'required|decimal|greater_than_equal_to[0]',
        'max_amount'      => 'permit_empty|decimal',
        'max_distance_km' => 'permit_empty|decimal|greater_than_equal_to[0]',
        'is_active'       => 'required|in_list[0,1]',
        'apply_to_all'    => 'required|in_list[0,1]',
        'start_date'      => 'required|valid_date',
        'end_date'        => 'required|valid_date',
        'product_ids'     => 'permit_empty' // Karena bisa null jika apply_to_all = 1
    ];

    /**
     * Ambil rule gratis ongkir yang berlaku (Memperhitungkan Waktu & Produk)
     */
    public function getApplicableRule(float $subtotal, ?float $distanceKm = null): ?array
    {
        $now = date('Y-m-d H:i:s');

        $builder = $this->where('is_active', 1)
                        ->where('start_date <=', $now) // Cek waktu mulai
                        ->where('end_date >=', $now)   // Cek waktu selesai
                        ->where('min_amount <=', $subtotal)
                        ->groupStart()
                            ->where('max_amount >=', $subtotal)
                            ->orWhere('max_amount IS NULL')
                        ->groupEnd();

        if ($distanceKm !== null) {
            $builder->groupStart()
                        ->where('max_distance_km >=', $distanceKm)
                        ->orWhere('max_distance_km IS NULL')
                     ->groupEnd();
        }

        return $builder->orderBy('min_amount', 'DESC')->first();
    }

    public function getActiveRules(): array
    {
        $now = date('Y-m-d H:i:s');
        return $this->where('is_active', 1)
                    ->where('start_date <=', $now)
                    ->where('end_date >=', $now)
                    ->orderBy('min_amount', 'ASC')
                    ->findAll();
    }
}