<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\CategoryModel;
use App\Models\OccasionModel;
use App\Models\ProductImageModel;
use App\Models\ProductModel;
use App\Models\DiscountRuleModel;
use App\Models\ProductVariantModel;
use App\Models\SubCategoryModel;
use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\HTTP\ResponseInterface;

class ShopController extends BaseController
{
    protected ProductModel $productModel;
    protected CategoryModel $categoryModel;
    protected OccasionModel $occasionModel;
    protected SubCategoryModel $subCategoryModel;
    protected ProductVariantModel $productVariantModel;
    protected ProductImageModel $productImageModel;

    public function __construct()
    {
        helper(['url']);
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->occasionModel = new OccasionModel();
        $this->subCategoryModel = new SubCategoryModel();
        $this->productVariantModel = new ProductVariantModel();
        $this->productImageModel = new ProductImageModel();
    }

    public function index(): ResponseInterface
    {
        $page       = max(1, (int) ($this->request->getGet('page') ?? 1));
        $perPage    = $this->resolvePerPage($this->request->getGet('per_page'));
        $keyword    = trim((string) ($this->request->getGet('keyword') ?? ''));
        $categoryId = $this->request->getGet('category');
        $occasionId = $this->request->getGet('occasion');

        $builder = $this->buildProductFilterQuery($categoryId, $occasionId, $keyword);

        $countBuilder   = clone $builder;
        $totalProducts  = (int) $countBuilder->countAllResults(false);
        $totalPages     = $totalProducts > 0 ? (int) ceil($totalProducts / $perPage) : 1;
        $offset         = ($page - 1) * $perPage;

        $products = $builder
            ->orderBy('products.tanggal_dibuat', 'DESC')
            ->limit($perPage, $offset)
            ->get()
            ->getResultArray();

        $response = [
            'filters' => [
                'categories' => $this->buildCategoryPayload(),
                'occasions'  => $this->occasionModel->findAll(),
            ],
            'products' => array_map(fn(array $product) => $this->transformProduct($product), $products),
            'pagination' => [
                'page'        => $page,
                'per_page'    => $perPage,
                'total'       => $totalProducts,
                'total_pages' => $totalPages,
                'has_next'    => $page < $totalPages,
                'has_prev'    => $page > 1,
            ],
            'selected' => [
                'category' => $categoryId,
                'occasion' => $occasionId,
                'keyword'  => $keyword,
            ],
        ];

        return $this->response->setJSON($response);
    }

    public function show(string $productId): ResponseInterface
    {
        $product = $this->productModel->getProductDetails($productId);

        if (!$product || (int) ($product['is_active'] ?? 0) !== 1) {
            return $this->response
                ->setStatusCode(ResponseInterface::HTTP_NOT_FOUND)
                ->setJSON(['message' => 'Product not found']);
        }

        $categoryDisplay = $this->buildCategoryDisplay($product);
        $mainImage = $this->buildProductImageUrl($product['gambar_url'] ?? '');

        $productPayload = [
            'id'              => $product['product_id'],
            'name'            => $product['nama_produk'],
            'description'     => $product['deskripsi_produk'],
            'price'           => (float) ($product['harga'] ?? 0),
            'price_formatted' => 'Rp' . number_format($product['harga'] ?? 0, 0, ',', '.'),
            'category'        => $categoryDisplay,
            'image_url'       => $mainImage,
            'detail_url'      => site_url('shop/product/' . $product['product_id']),
            'is_active'       => (int) ($product['is_active'] ?? 0),
        ];

        $variantsRaw = $this->productVariantModel->getVariantsByProductId($productId);
        $variants = [];
        foreach ($variantsRaw as $variant) {
            $variants[] = [
                'id'              => $variant['id'] ?? null,
                'name'            => $variant['name'] ?? 'Varian',
                'price'           => (float) ($variant['price'] ?? 0),
                'price_formatted' => 'Rp' . number_format($variant['price'] ?? 0, 0, ',', '.'),
            ];
        }

        if (empty($variants)) {
            $variants[] = [
                'id'              => null,
                'name'            => 'Default',
                'price'           => (float) ($product['harga'] ?? 0),
                'price_formatted' => 'Rp' . number_format($product['harga'] ?? 0, 0, ',', '.'),
            ];
        }

        $imagesRaw = $this->productImageModel->getImagesByProductId($productId);
        $images = [];
        foreach ($imagesRaw as $image) {
            $images[] = [
                'id'  => $image['id'] ?? null,
                'url' => $this->buildProductImageUrl($image['image_url'] ?? ''),
            ];
        }

        $relatedRaw = $this->productModel->getRelatedProducts($product['sub_category_id'] ?? null, $productId) ?? [];
        $relatedProducts = [];
        foreach ($relatedRaw as $related) {
            $relatedProducts[] = $this->transformProduct([
                'product_id'          => $related['product_id'],
                'nama_produk'         => $related['nama_produk'],
                'deskripsi_produk'    => $related['deskripsi_produk'],
                'harga'               => $related['harga'],
                'gambar_url'          => $related['gambar_url'],
                'category_display'    => $related['category_display'] ?? $this->buildCategoryDisplay($related),
                'min_variant_price'   => $related['min_variant_price'] ?? null,
                'max_variant_price'   => $related['max_variant_price'] ?? null,
            ]);
        }

        return $this->response->setJSON([
            'product'          => $productPayload,
            'variants'         => $variants,
            'images'           => $images,
            'related_products' => $relatedProducts,
        ]);
    }

    protected function buildProductFilterQuery($categoryId, $occasionId, ?string $keyword): BaseBuilder
    {
        $builder = $this->productModel->builder();

        $builder->select('
                products.product_id,
                products.nama_produk,
                products.deskripsi_produk,
                products.harga,
                products.gambar_url,
                products.is_active,
                COALESCE(MAX(sub_categories.sub_cat_name), MAX(categories.nama_kategori)) AS category_display,
                MIN(product_variants.price) AS min_variant_price,
                MAX(product_variants.price) AS max_variant_price
            ', false)
            ->join('sub_categories', 'sub_categories.sub_cat_id = products.sub_category_id', 'left')
            ->join('categories', 'categories.category_id = sub_categories.main_cat_id OR categories.category_id = products.sub_category_id', 'left')
            ->join('product_variants', 'product_variants.product_id = products.product_id', 'left')
            ->where('products.is_active', 1);

        if (!empty($occasionId)) {
            $builder->join('product_occasions', 'product_occasions.product_id = products.product_id', 'inner');
            $builder->where('product_occasions.occasion_id', $occasionId);
        }

        if (!empty($categoryId)) {
            $validIds = $this->resolveCategoryFilterIds($categoryId);
            if (!empty($validIds)) {
                $builder->whereIn('products.sub_category_id', $validIds);
            } else {
                $builder->where('products.sub_category_id', $categoryId);
            }
        }

        if (!empty($keyword)) {
            $builder->groupStart()
                    ->like('products.nama_produk', $keyword)
                    ->orLike('products.deskripsi_produk', $keyword)
                    ->groupEnd();
        }

        return $builder->groupBy('products.product_id');
    }

    protected function resolveCategoryFilterIds($categoryId): array
    {
        if (empty($categoryId)) {
            return [];
        }

        $subCategoryIds = $this->subCategoryModel
            ->where('main_cat_id', $categoryId)
            ->findColumn('sub_cat_id') ?? [];

        $subCategoryIds = array_map('intval', $subCategoryIds);

        return array_values(array_unique(array_merge([(int) $categoryId], $subCategoryIds)));
    }

    protected function buildCategoryPayload(): array
    {
        $categories = $this->categoryModel->findAll();
        if (empty($categories)) {
            return [];
        }

        $subCategories = $this->subCategoryModel->findAll();
        $subGrouped = [];
        foreach ($subCategories as $sub) {
            $subGrouped[$sub['main_cat_id']][] = [
                'id'   => $sub['sub_cat_id'],
                'name' => $sub['sub_cat_name'] ?? $sub['sub_cat_id'],
            ];
        }

        $payload = [];
        foreach ($categories as $category) {
            $payload[] = [
                'id'             => $category['category_id'],
                'name'           => $category['nama_kategori'],
                'sub_categories' => $subGrouped[$category['category_id']] ?? [],
            ];
        }

        return $payload;
    }
    
protected function transformProduct(array $product): array
    {
        $imageUrl = $this->buildProductImageUrl($product['gambar_url'] ?? '');
        $productId = $product['product_id'];

        // 1. Tentukan Harga Dasar (Gunakan harga varian termurah jika ada)
        $priceDisplay = (float) ($product['harga'] ?? 0);
        if (isset($product['min_variant_price']) && (float)$product['min_variant_price'] > 0) {
            $priceDisplay = (float) $product['min_variant_price'];
        }

        // 2. CEK DISKON DARI MODEL (Agar API tahu produk ini promo atau tidak)
        $discountRuleModel = new \App\Models\DiscountRuleModel();
        $discountData = $discountRuleModel->getProductDiscount($productId, $priceDisplay);

        $finalPrice = $priceDisplay;
        $isDiscounted = false;
        $discountPercent = 0;
        $isFutureDiscount = false;
        $futureDiscountDate = null;
        $futureDiscountPercent = 0;

        if ($discountData) {
            $calcPct = 0;
            if (isset($discountData['discounted_price']) && $discountData['discounted_price'] > 0) {
                $calcPct = round((($priceDisplay - $discountData['discounted_price']) / $priceDisplay) * 100);
            } else {
                $calcPct = round($discountData['discount_percentage'] ?? 0);
            }
            
            if (!empty($discountData['valid_pickup_start_date']) && date('Y-m-d') < $discountData['valid_pickup_start_date']) {
                $isFutureDiscount = true;
                $futureDiscountDate = date('d M Y', strtotime($discountData['valid_pickup_start_date']));
                $futureDiscountPercent = $calcPct;
            } else {
                $finalPrice = (float) $discountData['discounted_price']; 
                $discountPercent = $calcPct;
                $isDiscounted = $finalPrice < $priceDisplay;
            }
        }

        // 3. Handle Variant Range Label
        $variantRange = null;
        if (isset($product['min_variant_price']) && (float)$product['min_variant_price'] > 0) {
            $min = (float) $product['min_variant_price'];
            $max = (float) $product['max_variant_price'];
            if ($min !== $max) {
                $variantRange = [
                    'min' => $min,
                    'max' => $max,
                    'label' => 'Rp' . number_format($min, 0, ',', '.') . ' - Rp' . number_format($max, 0, ',', '.'),
                ];
            }
        }

        // 4. Return JSON Payload untuk Frontend
        return [
            'id'                 => $productId,
            'name'               => $product['nama_produk'],
            'description'        => $product['deskripsi_produk'],

            // HARGA UTAMA (Harga setelah diskon/Harga yang dibayar)
            'price'              => $finalPrice,
            'price_formatted'    => $isDiscounted 
                                    ? 'Rp' . number_format($finalPrice, 0, ',', '.') . ' (Promo)' 
                                    : 'Rp' . number_format($finalPrice, 0, ',', '.'),

            // HARGA ASLI (Data ini yang akan DICORET oleh Frontend)
            'original_price'     => $priceDisplay,
            'original_formatted' => 'Rp' . number_format($priceDisplay, 0, ',', '.'),
            
            // FLAG DISKON (Frontend React akan mengecek field ini)
            'is_discounted'      => $isDiscounted, 
            'discount'           => $discountPercent,
            
            // FLAG TEASER BADGE MASA DEPAN
            'is_future_discount' => $isFutureDiscount,
            'future_discount_date' => $futureDiscountDate,
            'future_discount_percent' => $futureDiscountPercent,

            'variant_range'      => $variantRange,
            'image_url'          => $imageUrl,
            'category'           => $product['category_display'] ?? $this->buildCategoryDisplay($product),
            'detail_url'         => site_url('shop/product/' . $productId),
        ];
    }

    protected function buildCategoryDisplay(array $product): ?string
    {
        $main = $product['category_display'] ?? $product['nama_kategori'] ?? null;
        $sub  = $product['sub_cat_name'] ?? null;

        if ($main && $sub) {
            return trim($main . ' / ' . $sub);
        }

        return $main ?? $sub;
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

    protected function resolvePerPage($value, int $default = 9): int
    {
        $perPage = is_numeric($value) ? (int) $value : $default;
        return max(1, min($perPage, 48));
    }
}
