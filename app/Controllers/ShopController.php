<?php namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\SubCategoryModel;
use App\Models\DiscountRuleModel;

class ShopController extends BaseController
{
    protected $productModel;
    protected $categoryModel;
    protected $subCategoryModel;
    protected $discountRuleModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->subCategoryModel = new SubCategoryModel();
        $this->discountRuleModel = new DiscountRuleModel();
    }

   // Lokasi: app/Controllers/ShopController.php

public function index()
{
    // Inisialisasi model yang dibutuhkan
    $occasionModel = new \App\Models\OccasionModel();
    $categoryModel = new \App\Models\CategoryModel();
    $subCategoryModel = new \App\Models\SubCategoryModel(); // Pastikan ini ada
    $perPage = 9;

    // Ambil semua data filter dari request URL
    $keyword = $this->request->getGet('keyword');
    $occasionId = $this->request->getGet('occasion');
    $categoryId = $this->request->getGet('category');

    // Mulai query builder (Query ini sudah benar dari sebelumnya)
    $builder = $this->productModel
        ->select('
            products.*, 
            COALESCE(sub_categories.sub_cat_name, categories.nama_kategori) as category_display,
            MIN(product_variants.price) as min_price,
            MAX(product_variants.price) as max_price
        ', false)
        ->join('sub_categories', 'sub_categories.sub_cat_id = products.sub_category_id', 'left')
        ->join('categories', 'categories.category_id = sub_categories.main_cat_id OR categories.category_id = products.sub_category_id', 'left')
        ->join('product_variants', 'product_variants.product_id = products.product_id', 'left')
        ->where('products.is_active', 1); // Group By dipindahkan ke akhir

    // Terapkan filter occasion jika ada (logika ini tetap sama)
    if (!empty($occasionId)) {
        // Lakukan join DULU dengan tabel relasi product_occasions
        $builder->join('product_occasions', 'product_occasions.product_id = products.product_id');
        $builder->where('product_occasions.occasion_id', $occasionId);
    }

    // --- START: LOGIKA FILTER KATEGORI YANG DISEMPURNAKAN ---
    if (!empty($categoryId)) {
        // 1. Cari semua sub-kategori yang termasuk dalam kategori utama yang dipilih
        $subCategoryIds = $subCategoryModel->where('main_cat_id', $categoryId)->findColumn('sub_cat_id') ?? [];
        
        // 2. Buat daftar ID yang valid: ID kategori utama ITU SENDIRI + semua ID sub-kategorinya
        $validIds = array_merge([$categoryId], $subCategoryIds);

        // 3. Terapkan filter WHERE IN, yang akan mencari produk di kategori utama ATAU di salah satu sub-kategorinya
        $builder->whereIn('products.sub_category_id', $validIds);
    }
    // --- END: LOGIKA FILTER KATEGORI YANG DISEMPURNAKAN ---

    // Terapkan filter keyword jika ada (logika ini tetap sama)
    if (!empty($keyword)) {
        $builder->groupStart()
                ->like('products.nama_produk', $keyword)
                ->orLike('products.deskripsi_produk', $keyword)
                ->groupEnd();
    }
    
    // Terapkan Group By di akhir setelah semua join dan where
    $builder->groupBy('products.product_id, sub_categories.sub_cat_name, categories.nama_kategori');

    // Siapkan data untuk view
    $data = [
        'products'          => $builder->paginate($perPage, 'shop_group'),
        'pager'             => $this->productModel->pager,
        'occasions'         => $occasionModel->findAll(),
        'categories'        => $categoryModel->findAll(),
        'selectedOccasion'  => $occasionId,
        'selectedCategory'  => $categoryId,
        'keyword'           => $keyword,
    ];

    // Ambil semua produk yang memiliki diskon aktif
    $data['productDiscounts'] = $this->discountRuleModel->getProductsWithDiscounts();
    
    $data['store'] = $this->storeData;

    return view('shop', $data);

}

}
