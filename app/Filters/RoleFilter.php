<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class RoleFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $session = session();
        $role = $session->get('role');

        // Jika tidak login, langsung lempar ke halaman login
        if (!$session->get('isLoggedIn')) {
            return redirect()->to('admin/login'); // Sesuaikan URL login jika berbeda
        }

        // Jika halaman ini butuh role tertentu (ada argumen)
        if (!empty($arguments)) {
            // Cek apakah role user ada di dalam daftar yang diizinkan
            if (!in_array($role, $arguments)) {
                // Jika tidak diizinkan, lempar ke dashboard dengan pesan error
                return redirect()->to('/admin/dashboard')->with('error', 'Anda tidak memiliki izin untuk mengakses halaman tersebut.');
            }
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak perlu melakukan apa-apa di sini
    }
}