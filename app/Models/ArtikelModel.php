<?php

namespace App\Models;

use CodeIgniter\Model;

class ArtikelModel extends Model
{
    protected $table      = 'artikel';
    protected $primaryKey = 'id_artikel';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'judul',
        'isi',
        'gambar',
        'produk_terkait',
        'tanggal_dibuat',
        'slug'
       
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'tanggal_dibuat';
    protected $dateFormat    = 'datetime';

}