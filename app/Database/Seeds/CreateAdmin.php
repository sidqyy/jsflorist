<?php

namespace App\Database\Seeds;


use CodeIgniter\Database\Seeder;
use App\Models\UserModel;
use CodeIgniter\I18n\Time; // Gunakan Time untuk timestamp

class CreateAdmin extends Seeder
{
    
    public function run()
    {

        // 1. Data untuk tabel 'users' (tanpa kolom role)

        $userData = [
            'username'   => 'Florist', // Ganti jika perlu
            'email'      => 'florist@jsflorist.com', // Ganti jika perlu
            'password_hash'   => password_hash('florist123', PASSWORD_DEFAULT),
        ];

        // Masukkan data user dan dapatkan ID-nya
        $this->db->table('users')->insert($userData);
        $newUserId = $this->db->insertID();

        // Cek jika user berhasil dibuat
        if ($newUserId) {
            echo "User 'management_user' created successfully with ID: " . $newUserId . "\n";
            
            // 2. Data untuk tabel 'user_roles' untuk menghubungkan user dengan rolenya
            // Asumsi ID untuk role 'management' adalah 1 (sesuai SQL sebelumnya)
            $userRoleData = [
                'user_id' => $newUserId,
                'role_id' => 2 // ID untuk 'management'
            ];

            // Masukkan data ke tabel user_roles
            $this->db->table('user_roles')->insert($userRoleData);

            echo "Role 'management' assigned to user ID: " . $newUserId . "\n";
        } else {
            echo "Failed to create user.\n";
        }
    }
}
