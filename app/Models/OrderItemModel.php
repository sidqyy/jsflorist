<?php
namespace App\Models;

use CodeIgniter\Model;

class OrderItemModel extends Model
{
    protected $table        = 'order_items';
    protected $primaryKey   = 'order_item_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    // PASTIKAN SEMUA KOLOM DARI TABEL 'order_items' YANG ANDA KIRIM ADA DI SINI
    protected $allowedFields = [
        'order_id',    // NOT NULL
        'product_id',  // NOT NULL (VARCHAR(11) di DB Anda)
        'kuantitas',   // NOT NULL (ada default 1, tapi kita juga kirim)
        'harga_satuan',// NOT NULL
        'custom_details', // KOLOM BARU DITAMBAHKAN DI SINI
    ];

    protected $useTimestamps = false; // Tidak ada kolom timestamp di tabel ini
    protected $dateFormat    = 'datetime';

    // Kosongkan validasi jika validasi dilakukan di Controller
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = true;
}