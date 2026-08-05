<?php namespace App\Models;

use CodeIgniter\Model;

class ProductOccasionModel extends Model
{
    protected $table = 'product_occasions';
    // Karena primary key adalah gabungan, kita tidak menentukannya secara tunggal
    protected $primaryKey = null;
    protected $returnType = 'array';

    protected $allowedFields = ['product_id', 'occasion_id'];

    protected $useTimestamps = false;
}
