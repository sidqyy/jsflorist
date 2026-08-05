<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ArtikelModel;
use App\Models\ProductModel;
use CodeIgniter\HTTP\ResponseInterface;

class ArticleController extends BaseController
{
    protected ArtikelModel $artikelModel;
    protected ProductModel $productModel;

    public function __construct()
    {
        helper(['text', 'url']);
        $this->artikelModel = new ArtikelModel();
        $this->productModel = new ProductModel();
    }

    public function index(): ResponseInterface
    {
        $page    = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage = $this->resolvePerPage($this->request->getGet('per_page'), 6);
        $keyword = trim((string) ($this->request->getGet('keyword') ?? ''));

        $builder = $this->artikelModel->builder();

        if ($keyword !== '') {
            $builder->groupStart()
                    ->like('judul', $keyword)
                    ->orLike('isi', $keyword)
                    ->groupEnd();
        }

        $countBuilder = clone $builder;
        $totalItems   = (int) $countBuilder->select('COUNT(*) as total')->countAllResults();

        $offset = ($page - 1) * $perPage;

        $builder->select('id_artikel, judul, slug, gambar, isi, tanggal_dibuat')
                ->orderBy('tanggal_dibuat', 'DESC')
                ->limit($perPage, $offset);

        $records = $builder->get()->getResultArray();

        $articles = [];
        foreach ($records as $record) {
            $articles[] = [
                'id'            => $record['id_artikel'],
                'title'         => $record['judul'],
                'slug'          => $record['slug'],
                'published_at'  => $record['tanggal_dibuat'],
                'excerpt'       => $this->buildExcerpt($record['isi'] ?? ''),
                'image_url'     => $this->buildArticleImageUrl($record['gambar'] ?? ''),
                'detail_url'    => site_url('artikel/' . $record['slug']),
            ];
        }

        $response = [
            'data' => $articles,
            'pagination' => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $totalItems,
                'total_pages' => $totalItems > 0 ? (int) ceil($totalItems / $perPage) : 1,
                'has_next'    => $page * $perPage < $totalItems,
                'has_prev'    => $page > 1,
            ],
            'filters' => [
                'keyword' => $keyword,
            ],
        ];

        return $this->response->setJSON($response);
    }

    public function show(string $slug): ResponseInterface
    {
        $article = $this->artikelModel->where('slug', $slug)->first();

        if (!$article) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['message' => 'Article not found']);
        }

        $relatedProducts = $this->resolveRelatedProducts($article['produk_terkait'] ?? null);

        return $this->response->setJSON([
            'article' => [
                'id'            => $article['id_artikel'],
                'title'         => $article['judul'],
                'slug'          => $article['slug'],
                'content_html'  => $article['isi'],
                'image_url'     => $this->buildArticleImageUrl($article['gambar'] ?? ''),
                'published_at'  => $article['tanggal_dibuat'],
                'detail_url'    => site_url('artikel/' . $article['slug']),
            ],
            'related_products' => $relatedProducts,
        ]);
    }

    protected function resolveRelatedProducts($rawValue): array
    {
        if (empty($rawValue)) {
            return [];
        }

        if (is_string($rawValue)) {
            $decoded = json_decode($rawValue, true);
        } elseif (is_array($rawValue)) {
            $decoded = $rawValue;
        } else {
            $decoded = [];
        }

        if (!is_array($decoded) || empty($decoded)) {
            return [];
        }

        $decoded = array_values(array_filter($decoded, static fn($id) => !empty($id)));
        if (empty($decoded)) {
            return [];
        }

        $products = $this->productModel
            ->select('product_id, nama_produk, deskripsi_produk, harga, gambar_url')
            ->whereIn('product_id', $decoded)
            ->findAll();

        if (empty($products)) {
            return [];
        }

        $productMap = [];
        foreach ($products as $product) {
            $productMap[$product['product_id']] = $product;
        }

        $ordered = [];
        foreach ($decoded as $productId) {
            if (!isset($productMap[$productId])) {
                continue;
            }

            $item = $productMap[$productId];
            $ordered[] = [
                'id'             => $item['product_id'],
                'name'           => $item['nama_produk'],
                'description'    => $item['deskripsi_produk'],
                'price'          => (float) ($item['harga'] ?? 0),
                'price_formatted'=> 'Rp' . number_format($item['harga'] ?? 0, 0, ',', '.'),
                'image_url'      => $this->buildProductImageUrl($item['gambar_url'] ?? ''),
                'detail_url'     => site_url('shop/product/' . $item['product_id']),
            ];
        }

        return $ordered;
    }

    protected function buildExcerpt(string $content, int $limit = 140): string
    {
        $plain = trim(strip_tags($content));
        if ($plain === '') {
            return '';
        }

        return character_limiter($plain, $limit);
    }

    protected function buildArticleImageUrl(?string $fileName): ?string
    {
        if (empty($fileName)) {
            return null;
        }

        if (preg_match('#^https?://#i', $fileName)) {
            return $fileName;
        }

        $path = ltrim($fileName, '/');
        if (str_starts_with($path, 'assets/') || str_starts_with($path, 'uploads/')) {
            return base_url($path);
        }

        return base_url('assets/img/artikel/' . $path);
    }

    protected function buildProductImageUrl(?string $fileName): ?string
    {
        if (empty($fileName)) {
            return null;
        }

        if (preg_match('#^https?://#i', $fileName)) {
            return $fileName;
        }

        $path = ltrim($fileName, '/');
        if (str_starts_with($path, 'assets/') || str_starts_with($path, 'uploads/')) {
            return base_url($path);
        }

        return base_url('assets/img/gambar/' . $path);
    }

    protected function resolvePerPage($value, int $default): int
    {
        $perPage = is_numeric($value) ? (int) $value : $default;
        return max(1, min($perPage, 24));
    }
}
