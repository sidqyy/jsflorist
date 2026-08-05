<?php namespace App\Models;

use CodeIgniter\Model;

class ProductComponentModel extends Model
{
    protected $table      = 'product_components';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['product_id', 'component_name', 'quantity', 'unit_cost', 'sort_order'];

    protected $useTimestamps = false; // Jika tidak menggunakan created_at/updated_at di tabel ini
    // protected $createdField  = 'created_at';
    // protected $updatedField  = 'updated_at';

    protected $validationRules    = [];
    protected $validationMessages = [];
    protected $skipValidation     = false;
}