<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberPointModel extends Model
{
    protected $table            = 'member_points';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'member_id',
        'points',
        'type',
        'source',
        'reference_id',
        'note',
        'created_at',
    ];

    protected $useTimestamps = false;
}
