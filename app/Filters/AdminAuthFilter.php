<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class AdminAuthFilter implements FilterInterface
{
    /**
     * This method is called before a controller is executed.
     *
     * @param RequestInterface $request
     * @param array|null       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        // 1. Dapatkan service session
        $session = session();

        // 2. Periksa apakah session 'isLoggedIn' tidak ada atau false
        if (!$session->get('isLoggedIn')) {
            // Jika belum login, arahkan ke halaman login
            return redirect()->to('admin/login'); // Ganti '/login' jika URL login Anda berbeda
        }

        // 3. Periksa apakah session 'role' bukan 'admin'
        // (Asumsi saat login Anda menyimpan role pengguna di session)
        if ($session->get('role') !== 'admin') {
            // Jika bukan admin, arahkan ke halaman lain (misalnya dashboard user)
            // dan berikan pesan error.
            return redirect()->to('/')->with('error', 'Anda tidak memiliki hak akses ke halaman ini.');
        }
    }

    /**
     * This method is called after a controller has finished execution.
     *
     * @param RequestInterface  $request
     * @param ResponseInterface $response
     * @param array|null        $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Tidak ada aksi yang perlu dilakukan setelah controller
    }
}