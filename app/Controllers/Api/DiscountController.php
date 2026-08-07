<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\DiscountRuleModel;
use App\Models\ProductModel;
use CodeIgniter\HTTP\ResponseInterface;

class DiscountController extends BaseController
{
    protected DiscountRuleModel $discountModel;
    protected ProductModel $productModel;

    public function __construct()
    {
        $this->discountModel = new DiscountRuleModel();
        $this->productModel = new ProductModel();
    }

    /**
     * GET /api/discounts
     */
    public function index(): ResponseInterface
    {
        $type = $this->request->getGet('type') ?? 'all';
        $includeExpired = $this->request->getGet('include_expired') === '1';

        $query = $this->discountModel->where('is_active', 1);

        if ($type !== 'all') {
            $query->where('discount_type', $type);
        }

        $discounts = $query->orderBy('discount_type', 'ASC')
                          ->orderBy('min_amount', 'ASC')
                          ->findAll();

        if (!$includeExpired) {
            $discounts = array_filter($discounts, fn($d) => $this->discountModel->isDiscountValid($d));
        }

        $result = array_map(fn($d) => $this->transformDiscount($d), array_values($discounts));

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $result,
            'meta' => [
                'total' => count($result),
                'type_filter' => $type,
            ],
        ]);
    }

    /**
     * GET /api/discounts/(:num)
     */
    public function show(int $id): ResponseInterface
    {
        $discount = $this->discountModel->find($id);

        if (!$discount) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Diskon tidak ditemukan.',
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => $this->transformDiscount($discount, true),
        ]);
    }

    /**
     * GET /api/discounts/product/(:any)
     */
    public function forProduct(string $productId): ResponseInterface
    {
        $product = $this->productModel->find($productId);

        if (!$product) {
            return $this->response->setStatusCode(404)->setJSON([
                'status' => 'error',
                'message' => 'Produk tidak ditemukan.',
            ]);
        }

        $originalPrice = (float) $product['harga'];
        $discount = $this->discountModel->getProductDiscount($productId, $originalPrice);

        if (!$discount) {
            return $this->response->setJSON([
                'status' => 'success',
                'data' => [
                    'has_discount' => false,
                    'product_id' => $productId,
                ],
            ]);
        }

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'has_discount' => true,
                'product_id' => $productId,
                'discount' => [
                    'id' => $discount['discount_id'],
                    'name' => $discount['discount_name'],
                    'discount_percentage' => $discount['discount_percentage'],
                    'discount_amount' => $discount['discount_amount'],
                ],
                'product' => [
                    'id' => $productId,
                    'name' => $product['nama_produk'],
                    'original_price' => $originalPrice,
                    'discounted_price' => $discount['discounted_price'],
                    'discount_amount' => $discount['discount_amount'],
                    'discount_percentage' => $discount['discount_percentage'],
                ],
            ],
        ]);
    }

    /**
     * POST /api/discounts/calculate
     */
    public function calculate(): ResponseInterface
    {
        $payload = $this->getJsonPayload();
        log_message('error', 'DISCOUNT CALCULATE CALLED: ' . json_encode($payload));
        $cartItems = $payload['cart_items'] ?? [];

        if (empty($cartItems)) {
            return $this->response->setStatusCode(400)->setJSON([
                'status' => 'error',
                'message' => 'Cart items kosong.',
            ]);
        }

        $subtotal = (float) ($payload['subtotal'] ?? 0);
        if ($subtotal <= 0) {
            foreach ($cartItems as $item) {
                $subtotal += ((float)($item['price'] ?? 0)) * ((int)($item['quantity'] ?? 1));
            }
        }

        // 1. Hitung Diskon Subtotal
        $subtotalDiscount = $this->discountModel->getApplicableDiscount($subtotal);
        $subtotalDiscountAmount = 0.0;
        if ($subtotalDiscount) {
            $subtotalDiscountAmount = $subtotal * ((float)$subtotalDiscount['discount_percentage'] / 100);
        }

        // 2. Hitung Diskon Produk (Format Baru)
        $productDiscounts = [];
        $productDiscountTotal = 0.0;

        foreach ($cartItems as $item) {
            $productId = $item['id'] ?? null;
            if (!$productId) continue;

            $itemPrice = (float) ($item['price'] ?? 0);
            $itemQty = (int) ($item['quantity'] ?? 1);
            
            $pDiscount = $this->discountModel->getProductDiscount($productId, $itemPrice);
            
            if ($pDiscount) {
                $discountAmt = (float)$pDiscount['discount_amount'] * $itemQty;
                $productDiscounts[] = [
                    'product_id' => $productId,
                    'original_price' => $itemPrice,
                    'discounted_price' => $pDiscount['discounted_price'],
                    'discount_amount' => round($discountAmt, 2),
                    'discount_name' => $pDiscount['discount_name'],
                ];
                $productDiscountTotal += $discountAmt;
            }
        }

        $totalDiscount = $subtotalDiscountAmount + $productDiscountTotal;

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'subtotal' => round($subtotal, 2),
                'total_discount' => round($totalDiscount, 2),
                'final_total' => round(max(0, $subtotal - $totalDiscount), 2),
                'subtotal_discount' => round($subtotalDiscountAmount, 2),
                'details' => [
                    'subtotal_discount' => round($subtotalDiscountAmount, 2),
                    'product_discounts' => empty($productDiscounts) ? null : $productDiscounts,
                ],
            ],
        ]);
    }

    /**
     * GET /api/discounts/products-with-discount
     * Diperbaiki agar membaca format JSON Baru
     */
    public function productsWithDiscount(): ResponseInterface
    {
        $productDiscounts = $this->discountModel->getActiveProductDiscounts();
        $result = [];
        $addedProductIds = [];

        foreach ($productDiscounts as $discount) {
            $productData = json_decode($discount['product_ids'] ?? '[]', true) ?? [];

            foreach ($productData as $pid => $info) {
                if (in_array($pid, $addedProductIds)) continue;

                $product = $this->productModel->find($pid);
                if ($product && (int)($product['is_active'] ?? 0) === 1) {
                    // Ambil harga dari JSON jika ada, jika tidak (format lama) hitung dari persen
                    $discountedPrice = isset($info['discounted_price']) ? (float)$info['discounted_price'] : $this->calculateDiscountedPrice((float)$product['harga'], (float)$discount['discount_percentage']);
                    
                    $result[] = [
                        'product' => [
                            'id' => $product['product_id'],
                            'name' => $product['nama_produk'],
                            'original_price' => (float)$product['harga'],
                            'discounted_price' => $discountedPrice,
                            'image_url' => $this->buildProductImageUrl($product['gambar_url'] ?? ''),
                        ],
                        'discount_name' => $discount['name']
                    ];
                    $addedProductIds[] = $pid;
                }
            }
        }

        return $this->response->setJSON(['status' => 'success', 'data' => $result]);
    }

    /**
     * Transform Data (PENTING)
     */
    private function transformDiscount(array $discount, bool $includeDetails = false): array
    {
        $productData = json_decode($discount['product_ids'] ?? '[]', true) ?? [];
        
        $result = [
            'id' => (int) $discount['discount_id'],
            'name' => $discount['name'] ?? null,
            'type' => $discount['discount_type'] ?? 'subtotal',
            'is_active' => (int) $discount['is_active'] === 1,
            'usage' => [
                'count' => (int) ($discount['usage_count'] ?? 0),
                'limit' => $discount['usage_limit'] ? (int) $discount['usage_limit'] : null,
            ],
        ];

        if ($result['type'] === 'subtotal') {
            $result['min_amount'] = (float)$discount['min_amount'];
            $result['percentage'] = (float)$discount['discount_percentage'];
        } else {
            // Mengirim daftar ID saja untuk kompatibilitas frontend dasar
            $result['product_ids'] = array_keys($productData); 
            $result['product_prices'] = $productData; // Mengirim data lengkap harga
        }

        return $result;
    }

    private function calculateDiscountedPrice(float $originalPrice, float $discountPercentage): float
    {
        return round($originalPrice * (1 - ($discountPercentage / 100)), 2);
    }

    private function buildProductImageUrl(?string $imagePath): string
    {
        if (empty($imagePath)) return base_url('assets/img/placeholder.png');
        if (str_starts_with($imagePath, 'http')) return $imagePath;
        return base_url($imagePath);
    }

    private function getJsonPayload(): array
    {
        $body = $this->request->getBody();
        $decoded = json_decode($body, true);
        return is_array($decoded) ? $decoded : ($this->request->getPost() ?? []);
    }
}