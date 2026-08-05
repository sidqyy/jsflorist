<?php

namespace App\Models;

use CodeIgniter\Model;

class FlowerTypeModel extends Model
{
    protected $table      = 'flower_types';
    protected $primaryKey = 'flower_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'nama_bunga',
        'deskripsi_bunga',
        'warna_utama',
        'gambar_bunga_url',
    ];

    protected $useTimestamps = false; // Tidak ada kolom timestamp di tabel flower_types
    protected $dateFormat    = 'datetime';

    protected $validationRules = [
        'nama_bunga'       => 'required|min_length[3]|max_length[100]|is_unique[flower_types.nama_bunga]',
        'deskripsi_bunga'  => 'permit_empty',
        'warna_utama'      => 'permit_empty|max_length[50]',
        'gambar_bunga_url' => 'permit_empty|valid_url',
    ];

    protected $validationMessages = [
        'nama_bunga' => [
            'is_unique' => 'Nama bunga ini sudah ada.',
            'required' => 'Nama bunga wajib diisi.'
        ]
    ];

    protected $skipValidation = false;
}