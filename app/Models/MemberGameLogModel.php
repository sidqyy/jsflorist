<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberGameLogModel extends Model
{
    protected $table            = 'member_game_logs';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'member_id',
        'game_id',
        'points_awarded',
        'result',
        'played_at',
        'metadata',
    ];

    protected $useTimestamps = false;
}
