<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ApiTokenModel;
use App\Models\CustomProductRequestModel;
use App\Models\ProductModel;
use CodeIgniter\HTTP\ResponseInterface;

class CustomOrderController extends BaseController
{
    protected ProductModel $productModel;
    protected CustomProductRequestModel $requestModel;
    protected ApiTokenModel $apiTokenModel;

    public function __construct()
    {
        helper(['url']);
        $this->productModel = new ProductModel();
        $this->requestModel = new CustomProductRequestModel();
        $this->apiTokenModel = new ApiTokenModel();
    }

    /**
     * Validasi data custom checkout untuk PRDKCUST dan kembalikan ringkasan.
     */
    public function checkout(): ResponseInterface
    {
        $payload = $this->getJsonPayload();
        $productId = trim((string) ($payload['product_id'] ?? ''));
        $customDetails = $payload['custom_details'] ?? [];

        $allowedCustomProducts = ['PRDKCUST', 'PRDKCUST1'];
        if (!in_array($productId, $allowedCustomProducts, true)) {
            return $this->respondError('Tipe produk tidak valid untuk permintaan kustom yang perlu ditinjau.', 400);
        }

        $jenisItem = trim((string) ($customDetails['jenis_item'] ?? ''));
        $jumlahItem = trim((string) ($customDetails['jumlah_item'] ?? ''));
        if ($jenisItem === '' || $jumlahItem === '') {
            return $this->respondError('Jenis Item dan Jumlah Item wajib diisi.', 400);
        }

        $product = $this->productModel->find($productId);
        if (!$product) {
            return $this->respondError('Produk kustom tidak ditemukan.', 404);
        }

        return $this->respondSuccess('OK', [
            'product' => $product,
            'custom_details' => [
                'jenis_item' => $jenisItem,
                'jumlah_item' => $jumlahItem,
                'bunga' => $customDetails['bunga'] ?? [],
            ],
            'info' => [
                'min_lead_time_hours' => 2,
                'timezone' => 'Asia/Makassar',
                'notice' => 'Untuk item kustom, mohon antar item ke toko maksimal 2 jam sebelum waktu pengantaran.',
            ],
        ]);
    }

    /**
     * Simpan permintaan custom PRDKCUST.
     */
    public function create(): ResponseInterface
    {
        $payload = $this->getJsonPayload();
        $productId = trim((string) ($payload['product_id'] ?? ''));

        $allowedCustomProducts = ['PRDKCUST', 'PRDKCUST1'];
        if (!in_array($productId, $allowedCustomProducts, true)) {
            return $this->respondError('Tipe produk tidak valid untuk disimpan sebagai permintaan kustom.', 400);
        }

        $customDetails = $payload['custom_details'] ?? [];
        $jenisItem = trim((string) ($customDetails['jenis_item'] ?? ''));
        $jumlahItem = trim((string) ($customDetails['jumlah_item'] ?? ''));

        $namaPemesan = trim((string) ($payload['nama_pemesan'] ?? ''));
        $nomorPemesan = trim((string) ($payload['nomor_pemesan'] ?? ''));
        $tanggalPengantaran = trim((string) ($payload['tanggal_pengantaran'] ?? ''));

        if ($jenisItem === '' || $jumlahItem === '' || $namaPemesan === '' || $nomorPemesan === '' || $tanggalPengantaran === '') {
            return $this->respondError('Data permintaan belum lengkap.', 400);
        }

        $witaTimeZone = new \DateTimeZone('Asia/Makassar');
        $requestedDateTime = \DateTime::createFromFormat('Y-m-d\TH:i', $tanggalPengantaran, $witaTimeZone);
        $errors = \DateTime::getLastErrors();

        if (!$requestedDateTime || ($errors['warning_count'] ?? 0) > 0 || ($errors['error_count'] ?? 0) > 0) {
            return $this->respondError('Format tanggal dan waktu tidak valid. Gunakan YYYY-MM-DDTHH:mm.', 400);
        }

        $now = new \DateTime('now', $witaTimeZone);
        $now->add(new \DateInterval('PT2H'));

        if ($requestedDateTime < $now) {
            return $this->respondError('Waktu pengantaran minimal harus 2 jam dari sekarang (WITA).', 400);
        }

        $userId = $this->getAuthenticatedUserId();
        if (!$userId) {
            $userId = (int) ($payload['user_id'] ?? 0) ?: null;
        }

        $flowers = $customDetails['bunga'] ?? null;
        if (is_array($flowers) && $flowers === []) {
            $flowers = null;
        }

        $dataToSave = [
            'user_id' => $userId,
            'product_template_id' => $productId,
            'item_type' => $jenisItem,
            'item_quantity' => $jumlahItem,
            'requested_flowers' => is_array($flowers) ? json_encode($flowers) : null,
            'additional_notes' => isset($payload['additional_notes']) ? (string) $payload['additional_notes'] : null,
            'delivery_date_requested' => $requestedDateTime->format('Y-m-d H:i:s'),
            'nama_pemesan' => $namaPemesan,
            'nomor_pemesan' => $nomorPemesan,
            'request_status' => 'Menunggu Review',
        ];

        if (!$this->requestModel->save($dataToSave)) {
            return $this->respondError('Gagal menyimpan permintaan. Silakan coba lagi.', 500);
        }

        return $this->respondSuccess('Pesanan custom berhasil disimpan.', [
            'request_id' => (int) $this->requestModel->getInsertID(),
            'status' => 'Menunggu Review',
        ], 201);
    }

    protected function getJsonPayload(): array
    {
        $payload = $this->request->getJSON(true);
        if (is_array($payload)) {
            return $payload;
        }

        $raw = $this->request->getBody();
        if ($raw) {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                return $decoded;
            }
        }

        return $this->request->getPost() ?? [];
    }

    protected function getAuthenticatedUserId(): ?int
    {
        $header = $this->request->getHeaderLine('Authorization');
        if (!$header || stripos($header, 'Bearer ') !== 0) {
            return null;
        }

        $token = trim(substr($header, 7));
        if ($token === '') {
            return null;
        }

        $tokenHash = hash('sha256', $token);
        $now = date('Y-m-d H:i:s');

        $tokenRow = $this->apiTokenModel
            ->where('token_hash', $tokenHash)
            ->where('revoked_at', null)
            ->groupStart()
                ->where('expires_at', null)
                ->orWhere('expires_at >=', $now)
            ->groupEnd()
            ->first();

        if (!$tokenRow) {
            return null;
        }

        $this->apiTokenModel->update($tokenRow['id'], ['last_used_at' => $now]);

        return (int) $tokenRow['user_id'];
    }

    protected function respondSuccess(string $message, array $data = [], int $statusCode = 200): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON([
                'status' => 'success',
                'message' => $message,
                'data' => $data,
            ]);
    }

    protected function respondError(string $message, int $statusCode): ResponseInterface
    {
        return $this->response
            ->setStatusCode($statusCode)
            ->setJSON([
                'status' => 'error',
                'message' => $message,
            ]);
    }
}
