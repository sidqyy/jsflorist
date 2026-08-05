<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberGameModel extends Model
{
    protected $table            = 'member_games';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'name',
        'is_active',
        'points_min',
        'points_max',
        'daily_limit',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';
}
