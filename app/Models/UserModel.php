<?php

namespace App\Models;

use CodeIgniter\Model;

class UserModel extends Model
{
    protected $table      = 'users';
    protected $primaryKey = 'user_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array'; // Atau 'object'
    protected $useSoftDeletes = false; // Jika tidak menggunakan soft delete

    // Kolom-kolom yang diizinkan untuk diisi melalui insert/update
    protected $allowedFields = [
        'nama_depan',
        'nama_belakang',
        'email',
        'password_hash',
        'nomor_hp',
        'tanggal_daftar', // Biasanya diatur otomatis oleh DB
        'last_login',
    ];

    // Timestamp
    protected $useTimestamps = true;
    protected $createdField  = 'tanggal_daftar'; // Nama kolom untuk created_at
    protected $updatedField  = 'last_login'; // Nama kolom untuk updated_at (kita pakai last_login sebagai contoh)
    protected $dateFormat    = 'datetime'; // Format tanggal

    // Validasi
    protected $validationRules = [
        'nama_depan' => 'required|min_length[3]|max_length[100]',
        'email'      => 'required|valid_email|is_unique[users.email]',
        'password_hash' => 'required|min_length[8]', // Password di-hash sebelum disimpan
        'nomor_hp'   => 'permit_empty|max_length[20]',
    ];

    protected $validationMessages = [
        'email' => [
            'is_unique' => 'Maaf, email ini sudah terdaftar.',
            'valid_email' => 'Format email tidak valid.'
        ],
        'password_hash' => [
            'required' => 'Kata sandi wajib diisi.',
            'min_length' => 'Kata sandi minimal 8 karakter.'
        ]
    ];

    protected $skipValidation = false; // Pastikan validasi aktif
    protected $cleanValidationRules = true; // Membersihkan aturan validasi yang tidak digunakan

    // Callbacks
    protected $beforeInsert = ['hashPassword'];
    protected $beforeUpdate = ['hashPassword'];

    protected function hashPassword(array $data)
    {
        if (isset($data['data']['password'])) {
            $data['data']['password_hash'] = password_hash($data['data']['password'], PASSWORD_DEFAULT);
            unset($data['data']['password']); // Hapus password asli setelah di-hash
        }
        return $data;
    }
}