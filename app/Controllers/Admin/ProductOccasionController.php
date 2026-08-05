<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\OccasionModel;
use App\Models\ProductModel;
use App\Models\ProductOccasionModel;

class ProductOccasionController extends BaseController
{
    protected $occasionModel;
    protected $productModel;
    protected $productOccasionModel;

    public function __construct()
    {
        $this->occasionModel = new OccasionModel();
        $this->productModel = new ProductModel();
        $this->productOccasionModel = new ProductOccasionModel();
    }

    public function index()
    {
        $occasions = $this->occasionModel->findAll();
        
        return view('admin/product-occasions/index', [
            'occasions' => $occasions
        ]);
    }

   public function products($occasionId)
    {
        $occasion = $this->occasionModel->find($occasionId);
        if (!$occasion) {
            return redirect()->to('/admin/product-occasions')->with('error', 'Occasion tidak ditemukan.');
        }

        // Ambil parameter filter
        $categoryId = $this->request->getGet('category');
        
        // Ambil produk yang sudah ada di occasion ini
        $existingProducts = $this->productOccasionModel
            ->where('occasion_id', $occasionId)
            ->findAll();
        $existingProductIds = array_column($existingProducts, 'product_id');

        // Query produk dengan filter kategori
        $builder = $this->productModel
            ->select('products.*, COALESCE(sub_categories.sub_cat_name, categories.nama_kategori) as category_display', false)
            ->join('sub_categories', 'sub_categories.sub_cat_id = products.sub_category_id', 'left')
            ->join('categories', 'categories.category_id = sub_categories.main_cat_id', 'left');

        // Apply filter jika ada
        if ($categoryId) {
            $builder->where('products.sub_category_id', $categoryId);
        }

        $products = $builder->findAll();

        // Ambil semua kategori untuk filter
        $categories = $this->productModel
            ->select('DISTINCT products.sub_category_id, COALESCE(sub_categories.sub_cat_name, categories.nama_kategori) as category_name', false)
            ->join('sub_categories', 'sub_categories.sub_cat_id = products.sub_category_id', 'left')
            ->join('categories', 'categories.category_id = sub_categories.main_cat_id', 'left')
            ->findAll();

        return view('admin/product-occasions/products', [
            'occasion' => $occasion,
            'products' => $products,
            'existingProductIds' => $existingProductIds,
            'categories' => $categories,
            'selectedCategory' => $categoryId
        ]);
    }

    public function addProducts()
    {
        $occasionId = $this->request->getPost('occasion_id');
        // Ambil product_ids, jika tidak ada, anggap array kosong
        $productIds = $this->request->getPost('product_ids') ?? [];

        if (empty($occasionId)) {
            return redirect()->back()->with('error', 'Occasion ID tidak valid.');
        }

        // Ambil ID produk yang sudah ada di occasion ini
        $existingProducts = $this->productOccasionModel
            ->where('occasion_id', $occasionId)
            ->findColumn('product_id') ?? [];

        // Tentukan produk yang akan ditambahkan dan dihapus
        $productsToAdd = array_diff($productIds, $existingProducts);
        $productsToRemove = array_diff($existingProducts, $productIds);

        // Tambahkan produk baru
        if (!empty($productsToAdd)) {
            $dataToInsert = [];
            foreach ($productsToAdd as $productId) {
                $dataToInsert[] = [
                    'product_id' => $productId,
                    'occasion_id' => $occasionId
                ];
            }
            $this->productOccasionModel->insertBatch($dataToInsert);
        }

        // Hapus produk yang tidak lagi dipilih
        if (!empty($productsToRemove)) {
            $this->productOccasionModel
                ->where('occasion_id', $occasionId)
                ->whereIn('product_id', $productsToRemove)
                ->delete();
        }

        return redirect()->to("/admin/product-occasions/products/$occasionId")
            ->with('success', 'Produk untuk occasion berhasil diperbarui.');
    }

}
