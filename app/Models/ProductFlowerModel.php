<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductFlowerModel extends Model
{
    protected $table      = 'product_flowers';
    protected $primaryKey = 'product_flower_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'product_id',
        'flower_id',
        'kuantitas',
    ];

    protected $useTimestamps = false; // Tidak ada kolom timestamp di tabel ini
    protected $dateFormat    = 'datetime';

    protected $validationRules = [
        'product_id' => 'required|integer',
        'flower_id'  => 'required|integer',
        'kuantitas'  => 'required|integer|greater_than_equal_to[1]',
    ];

    protected $validationMessages = []; // Tambahkan pesan kustom jika diperlukan

    protected $skipValidation = false;

    // Metode bantu untuk menambahkan bunga ke produk
    public function addFlowersToProduct(int $productId, array $flowerData)
    {
        $batch = [];
        foreach ($flowerData as $flower) {
            $batch[] = [
                'product_id' => $productId,
                'flower_id'  => $flower['flower_id'],
                'kuantitas'  => $flower['kuantitas'] ?? 1,
            ];
        }

        if (!empty($batch)) {
            return $this->insertBatch($batch);
        }
        return false;
    }

    // Metode bantu untuk mendapatkan bunga-bunga dari suatu produk
    public function getFlowersByProductId(int $productId)
    {
        return $this->select('product_flowers.*, flower_types.nama_bunga, flower_types.warna_utama')
                    ->join('flower_types', 'flower_types.flower_id = product_flowers.flower_id')
                    ->where('product_flowers.product_id', $productId)
                    ->findAll();
    }

    // Metode bantu untuk menghapus bunga dari suatu produk (misal: saat update)
    public function removeFlowersFromProduct(int $productId)
    {
        return $this->where('product_id', $productId)->delete();
    }
}