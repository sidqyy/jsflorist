<?php

namespace App\Models;

use CodeIgniter\Model;

class MemberVoucherModel extends Model
{
    protected $table            = 'member_vouchers';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'member_id',
        'voucher_code',
        'voucher_name',
        'discount_type',
        'discount_value',
        'min_amount',
        'max_amount',
        'expires_at',
        'status',
        'created_at',
        'used_at',
        'order_id',
    ];

    protected $useTimestamps = false;
}
