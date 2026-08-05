<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductVariantModel extends Model
{
    protected $table            = 'product_variants';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['product_id', 'name', 'price','gambar_varian_url'];

    public function getVariantsByProductId($productId)
    {
        return $this->where('product_id', $productId)->findAll();
    }
}