<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\UserModel;

class AuthController extends BaseController
{
    /**
     * Menampilkan halaman login.
     */
    public function login()
    {
        // Jika sudah login (sebagai management atau karyawan), langsung arahkan ke dashboard
        if (session()->get('isLoggedIn')) {
            return redirect()->to('/admin/dashboard');
        }

        $data = [
            'title' => 'Login Admin',
            'store' => $this->storeData
        ];

        return view('admin/auth/login', $data);
    }

    /**
     * Memproses percobaan login (autentikasi).
     * Logika ini sudah sepenuhnya menggunakan sistem role dari database.
     */
    public function authenticate()
    {
        $session = session();
        $userModel = new UserModel();

        // Aturan validasi input
        $rules = [
            'email'    => 'required|valid_email',
            'password' => 'required'
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $email = $this->request->getPost('email');
        $password = $this->request->getPost('password');

        // Cari pengguna di database beserta rolenya
        $user = $userModel
            ->select('users.*, roles.name as role_name')
            ->join('user_roles', 'user_roles.user_id = users.user_id')
            ->join('roles', 'roles.id = user_roles.role_id')
            ->where('users.email', $email)
            ->first();

        // Verifikasi pengguna dan password
        // Menggunakan kolom 'password' sesuai dengan seeder Anda
        if (!$user || !password_verify($password, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Email atau Password salah.');
        }

        // Jika semua berhasil, siapkan dan set data session
        $sessionData = [
            'user_id'    => $user['user_id'],
            'name'       => $user['username'],
            'email'      => $user['email'],
            'isLoggedIn' => true,
            'role'       => $user['role_name'] // Peran diambil dari database
        ];

        $session->set($sessionData);
        
        return redirect()->to('/admin/dashboard')->with('success', 'Login berhasil! Selamat datang, ' . $user['username']);
    }

    /**
     * Memproses logout.
     */
    public function logout()
    {
        session()->destroy();
        return redirect()->to('/admin/login')->with('success', 'Anda telah berhasil logout.');
    }
}
