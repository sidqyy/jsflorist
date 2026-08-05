<?php

namespace App\Controllers;

use CodeIgniter\Controller;
use CodeIgniter\Exceptions\PageNotFoundException;

class FileViewer extends BaseController
{
    public function viewProof($fileName)
    {
        // Path ke folder tempat file bukti pembayaran disimpan.
        // Gunakan ROOTPATH untuk memastikan path absolut dari root proyek.
        $filePath = ROOTPATH . 'public/uploads/proofs/' . $fileName;

        // Periksa apakah file ada dan dapat dibaca
        if (!is_file($filePath) || !is_readable($filePath)) {
            // Log error atau lemparkan exception jika file tidak ditemukan
            log_message('error', 'File not found or not readable: ' . $filePath);
            throw PageNotFoundException::forPageNotFound();
        }

        // Tentukan Content-Type berdasarkan tipe file
        $mime = mime_content_type($filePath);
        if ($mime === false) {
            // Fallback jika mime_content_type gagal
            $mime = 'application/octet-stream';
        }

        // Baca konten file
        $fileContent = file_get_contents($filePath);

        // Kirim file ke browser
        return $this->response
            ->setStatusCode(200)
            ->setContentType($mime)
            ->setBody($fileContent);
    }
}