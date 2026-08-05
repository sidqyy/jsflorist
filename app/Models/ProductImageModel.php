<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductImageModel extends Model
{
    protected $table            = 'product_images';
    protected $primaryKey       = 'id';
    protected $allowedFields    = ['product_id', 'image_url'];

    public function getImagesByProductId($productId)
    {
        return $this->where('product_id', $productId)->findAll();
    }
}