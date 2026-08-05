<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberGameProgressModel extends Model
{
    protected $table            = 'member_game_progress';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'user_id',
        'member_id',
        'current_level',
        'max_unlocked_level',
        'updated_at',
    ];

    protected $useTimestamps = false;
}
