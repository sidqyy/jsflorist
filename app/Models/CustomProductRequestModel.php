<?php
namespace App\Models;

use CodeIgniter\Model;

class CustomProductRequestModel extends Model
{
    protected $table            = 'custom_product_requests';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'user_id',
        'product_template_id',
        'item_type',
        'item_quantity',
        'requested_flowers',
        'additional_notes',
        'delivery_date_requested',
        'request_status',
        'nama_pemesan',
        'nomor_pemesan',
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
}