<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\ArtikelModel;
use App\Models\OccasionModel;
use App\Models\ProductModel;
use App\Models\ProductOccasionModel;
use CodeIgniter\HTTP\ResponseInterface;

class DashboardController extends BaseController
{
    protected OccasionModel $occasionModel;
    protected ProductOccasionModel $productOccasionModel;
    protected ProductModel $productModel;
    protected ArtikelModel $artikelModel;
    /**
     * Ordered list of bestseller product IDs used on the marketing dashboard.
     * Update this array whenever the curated list changes.
     */
    protected array $bestsellerProductIds = [
        'PRDK003',
        'PRDK005',
        'PRDK018',
        'PRDK023',
        'PRDK060',
        'PRDK061',
        'PRDK082',
        'PRDK085',
        'PRDK111',
        'PRDK114',
        'PRDK116',
        'PRDK137',
        'PRDK148',
        'PRDK151',
        'PRDK160',
    ];

    public function __construct()
    {
        helper(['url']);
        $this->occasionModel = new OccasionModel();
        $this->productOccasionModel = new ProductOccasionModel();
        $this->productModel = new ProductModel();
        $this->artikelModel = new ArtikelModel();
    }

    public function index(): ResponseInterface
    {
        $productLimit = $this->resolveLimit($this->request->getGet('product_limit'), 4);
        $articleLimit = $this->resolveLimit($this->request->getGet('article_limit'), 6);
        $bestsellerDefault = max(1, min(count($this->bestsellerProductIds), 24));
        $bestsellerLimit = $this->resolveLimit($this->request->getGet('bestseller_limit'), $bestsellerDefault);

        return $this->response->setJSON([
            'occasions' => $this->buildOccasionPayload($productLimit),
            'articles'  => $this->buildArticlePayload($articleLimit),
            'best_sellers' => $this->buildBestsellerPayload($bestsellerLimit),
        ]);
    }

    public function occasions(): ResponseInterface
    {
        $productLimit = $this->resolveLimit($this->request->getGet('product_limit'), 4);

        return $this->response->setJSON([
            'occasions' => $this->buildOccasionPayload($productLimit),
        ]);
    }

    public function articles(): ResponseInterface
    {
        $articleLimit = $this->resolveLimit($this->request->getGet('article_limit'), 6);

        return $this->response->setJSON([
            'articles' => $this->buildArticlePayload($articleLimit),
        ]);
    }

    public function bestSellers(): ResponseInterface
    {
        $bestsellerDefault = max(1, min(count($this->bestsellerProductIds), 24));
        $bestsellerLimit = $this->resolveLimit($this->request->getGet('bestseller_limit'), $bestsellerDefault);

        return $this->response->setJSON([
            'best_sellers' => $this->buildBestsellerPayload($bestsellerLimit),
        ]);
    }

    protected function buildOccasionPayload(int $productLimit): array
    {
        $occasions = $this->occasionModel->findAll();
        if (empty($occasions)) {
            return [];
        }

        $occasionIds = array_column($occasions, 'occasion_id');
        $relations = $this->productOccasionModel
            ->select('occasion_id, product_id')
            ->whereIn('occasion_id', $occasionIds)
            ->findAll();

        if (empty($relations)) {
            return [];
        }

        $productIds = array_values(array_unique(array_column($relations, 'product_id')));
        if (empty($productIds)) {
            return [];
        }

        $products = $this->productModel
            ->select('products.product_id, products.nama_produk, products.deskripsi_produk, products.harga, products.gambar_url, products.is_active, COALESCE(sub_categories.sub_cat_name, categories.nama_kategori) as category_display', false)
            ->join('sub_categories', 'sub_categories.sub_cat_id = products.sub_category_id', 'left')
            ->join('categories', 'categories.category_id = sub_categories.main_cat_id', 'left')
            ->whereIn('products.product_id', $productIds)
            ->where('products.is_active', 1)
            ->findAll();

        if (empty($products)) {
            return [];
        }

        $productMap = [];
        foreach ($products as $product) {
            $productMap[$product['product_id']] = $product;
        }

        $grouped = [];
        foreach ($occasions as $occasion) {
            $grouped[$occasion['occasion_id']] = [
                'occasion_id'   => $occasion['occasion_id'],
                'occasion_name' => $occasion['occasion_name'],
                'products'      => [],
            ];
        }

        foreach ($relations as $relation) {
            $occasionId = $relation['occasion_id'];
            $productId  = $relation['product_id'];

            if (!isset($grouped[$occasionId], $productMap[$productId])) {
                continue;
            }

            if (count($grouped[$occasionId]['products']) >= $productLimit) {
                continue;
            }

            $grouped[$occasionId]['products'][] = $this->transformProduct($productMap[$productId]);
        }

        return array_values(array_filter($grouped, static fn(array $item) => !empty($item['products'])));
    }

    protected function buildArticlePayload(int $articleLimit): array
    {
        $articles = $this->artikelModel
            ->orderBy('tanggal_dibuat', 'DESC')
            ->findAll($articleLimit);

        if (empty($articles)) {
            return [];
        }

        $payload = [];
        foreach ($articles as $article) {
            $payload[] = [
                'id'            => $article['id_artikel'],
                'title'         => $article['judul'],
                'slug'          => $article['slug'],
                'published_at'  => $article['tanggal_dibuat'],
                'excerpt'       => $this->buildExcerpt($article['isi'] ?? ''),
                'image_url'     => $this->buildArticleImageUrl($article['gambar'] ?? ''),
                'detail_url'    => site_url('artikel/' . $article['slug']),
            ];
        }

        return $payload;
    }

    protected function buildBestsellerPayload(int $limit): array
    {
        if (empty($this->bestsellerProductIds)) {
            return [];
        }

        $targetIds = array_slice($this->bestsellerProductIds, 0, $limit);

        $products = $this->productModel
            ->select('products.product_id, products.nama_produk, products.deskripsi_produk, products.harga, products.gambar_url, products.is_active, COALESCE(sub_categories.sub_cat_name, categories.nama_kategori) as category_display', false)
            ->join('sub_categories', 'sub_categories.sub_cat_id = products.sub_category_id', 'left')
            ->join('categories', 'categories.category_id = sub_categories.main_cat_id', 'left')
            ->whereIn('products.product_id', $targetIds)
            ->where('products.is_active', 1)
            ->findAll();

        if (empty($products)) {
            return [];
        }

        $productMap = [];
        foreach ($products as $product) {
            $productMap[$product['product_id']] = $product;
        }

        $ordered = [];
        foreach ($targetIds as $productId) {
            if (!isset($productMap[$productId])) {
                continue;
            }
            $ordered[] = $this->transformProduct($productMap[$productId]);
        }

        return $ordered;
    }

    protected function transformProduct(array $product): array
    {
        return [
            'id'             => $product['product_id'],
            'name'           => $product['nama_produk'],
            'description'    => $product['deskripsi_produk'],
            'price'          => (float) ($product['harga'] ?? 0),
            'price_formatted'=> 'Rp' . number_format($product['harga'] ?? 0, 0, ',', '.'),
            'image_url'      => $this->buildProductImageUrl($product['gambar_url'] ?? ''),
            'category'       => $product['category_display'] ?? null,
            'detail_url'     => site_url('shop/product/' . $product['product_id']),
        ];
    }

    protected function buildExcerpt(string $content, int $limit = 120): string
    {
        $stripped = trim(strip_tags($content));
        if ($stripped === '') {
            return '';
        }

        if (mb_strlen($stripped) <= $limit) {
            return $stripped;
        }

        return mb_substr($stripped, 0, $limit - 3) . '...';
    }

    protected function buildProductImageUrl(string $fileName): ?string
    {
        if ($fileName === '') {
            return null;
        }

        return base_url('assets/img/gambar/' . ltrim($fileName, '/'));
    }

    protected function buildArticleImageUrl(string $fileName): ?string
    {
        if ($fileName === '') {
            return null;
        }

        return base_url('assets/img/artikel/' . ltrim($fileName, '/'));
    }

    protected function resolveLimit($value, int $default): int
    {
        $limit = is_numeric($value) ? (int) $value : $default;
        return ($limit > 0 && $limit <= 24) ? $limit : $default;
    }
}
