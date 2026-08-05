<?php namespace App\Models;

use CodeIgniter\Model;

class OccasionModel extends Model
{
    protected $table = 'occasions';
    protected $primaryKey = 'occasion_id';
    protected $returnType = 'array';

    protected $allowedFields = ['occasion_name'];

    // Aturan validasi opsional
    protected $validationRules = [
        'occasion_name' => 'required|max_length[100]|is_unique[occasions.occasion_name]',
    ];

    protected $skipValidation = false;
}
