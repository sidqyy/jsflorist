<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Database\Exceptions\DatabaseException; // Penting untuk menangkap error

class DbTest extends Controller
{
    public function index()
    {
        try {
            // Mencoba mendapatkan instance koneksi database
            $db = \Config\Database::connect();

            // Mencoba melakukan query sederhana untuk menguji koneksi
            // Misalnya, mengambil daftar tabel (ini akan berbeda tergantung DB Driver)
            // Untuk MySQL/MariaDB:
            $query = $db->query("SHOW TABLES");
            // Atau yang lebih generik:
            // $query = $db->query("SELECT 1"); // Query paling sederhana

            if ($query) {
                echo "<h1>Koneksi Database BERHASIL!</h1>";
                echo "<p>Anda berhasil terhubung ke database: <strong>" . $db->getDatabase() . "</strong></p>";
                echo "<p>Driver: " . $db->DBDriver . "</p>";

                // Opsional: tampilkan beberapa tabel jika query SHOW TABLES
                echo "<h3>Tabel-tabel di database:</h3>";
                echo "<ul>";
                foreach ($query->getResultArray() as $row) {
                    foreach ($row as $table) {
                        echo "<li>" . $table . "</li>";
                    }
                }
                echo "</ul>";

            } else {
                echo "<h1>Koneksi Database GAGAL!</h1>";
                echo "<p>Query gagal, tapi koneksi awal mungkin berhasil. Periksa error query.</p>";
            }

        } catch (DatabaseException $e) {
            // Menangkap error jika koneksi gagal
            echo "<h1>Koneksi Database GAGAL!</h1>";
            echo "<p>Pesan Error: " . $e->getMessage() . "</p>";
            echo "<p>Periksa kembali konfigurasi database di <code>app/Config/Database.php</code>.</p>";
            echo "<p>Pastikan username, password, nama database, dan port sudah benar.</p>";
            echo "<p>Pastikan juga server database (MySQL/MariaDB) sedang berjalan.</p>";
        }
    }
}