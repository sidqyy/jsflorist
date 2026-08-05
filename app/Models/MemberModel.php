<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberModel extends Model
{
    protected $table            = 'members';
    protected $primaryKey       = 'member_id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'user_id',
        'member_code',
        'tier',
        'points_balance',
        'total_points_earned',
        'total_points_redeemed',
        'status',
        'joined_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'joined_at';
    protected $updatedField  = 'updated_at';
    protected $dateFormat    = 'datetime';

    public function findByUserId(int $userId): ?array
    {
        return $this->where('user_id', $userId)->first();
    }
}
