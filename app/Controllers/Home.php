<?php

namespace App\Controllers;
use App\Models\CategoryModel; // Import CategoryModel
use App\Models\ProductModel;
use App\Models\SubCategoryModel; // Import SubCategoryModel
use App\Models\ArtikelModel;
use App\Models\OccasionModel;
use App\Models\ProductOccasionModel;
use App\Models\ProductVariantModel;
use App\Models\ProductImageModel;
use App\Models\EventBannerModel;
use App\Models\DiscountRuleModel;

class Home extends BaseController
{

     public function __construct()
    {
         $this->productModel = new ProductModel();
          $this->categoryModel = new CategoryModel();
        $this->subCategoryModel = new SubCategoryModel();
        $this->productVariantModel = new ProductVariantModel();
        $this->productImageModel = new ProductImageModel();
        $this->discountRuleModel = new DiscountRuleModel();
    }
   public function index()
    {
        $categoryModel = new CategoryModel();
        $productModel  = new ProductModel();
        $artikelModel = new ArtikelModel();
        $occasionModel = new OccasionModel();
        $productOccasionModel = new ProductOccasionModel();
        $eventBannerModel = new EventBannerModel();

        // Get all active event banners for current domain
        $currentDomain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $data['eventBanners'] = $eventBannerModel->getActiveEventBanners($currentDomain);

        // --- Fetch Occasions and their products ---
        $occasions = $occasionModel->findAll();
        $productsByOccasion = [];

        if (!empty($occasions)) {
            $occasionIds = array_column($occasions, 'occasion_id');
            $relations = $productOccasionModel->whereIn('occasion_id', $occasionIds)->findAll();

            if (!empty($relations)) {
                $productIds = array_unique(array_column($relations, 'product_id'));
                
                // Fetch products with their category name for display
                // Perbaiki join seperti di productDetail
                $products = $productModel
                    ->select('products.*, COALESCE(sub_categories.sub_cat_name, categories.nama_kategori) as category_display', false)
                    ->join('sub_categories', 'sub_categories.sub_cat_id = products.sub_category_id', 'left')
                    ->join('categories', 'categories.category_id = sub_categories.main_cat_id', 'left')
                    ->whereIn('products.product_id', $productIds)
                    ->where('products.is_active', 1)
                    ->findAll();
                    
                $productMap = [];
                foreach ($products as $product) {
                    $productMap[$product['product_id']] = $product;
                }

               foreach ($relations as $relation) {
    if (isset($productMap[$relation['product_id']])) {
        // Inisialisasi array jika belum ada
        if (!isset($productsByOccasion[$relation['occasion_id']])) {
            $productsByOccasion[$relation['occasion_id']] = [];
        }
        
        // Hanya tambahkan produk jika jumlahnya masih di bawah 4
        if (count($productsByOccasion[$relation['occasion_id']]) < 4) {
            $productsByOccasion[$relation['occasion_id']][] = $productMap[$relation['product_id']];
        }
    }
}
            }
        }
        $data['occasions'] = $occasions;
        $data['productsByOccasion'] = $productsByOccasion;

        // --- Logic for other sections (non-bouquet, bestsellers) ---
        $bouquetCategoryNames = [
            'Hand Bouquet - All Category',
            'Wedding Bouquet',
            'Graduation Bouquet',
            'Anniversarry Bouquet',
            'Baloon Bouquet',
            'Artificial Bouquet',
        ];
        $allCategories = $categoryModel->findAll();
        $nonBouquetCategoryIds = [];
        $bouquetCategoryIds = [];
        $categoryNamesMap = [];

        foreach ($allCategories as $category) {
            $categoryNamesMap[$category['category_id']] = $category['nama_kategori'];
            if (in_array($category['nama_kategori'], $bouquetCategoryNames)) {
                $bouquetCategoryIds[] = $category['category_id'];
            } else {
                $nonBouquetCategoryIds[] = $category['category_id'];
            }
        }
        $data['allExistingCategories'] = $allCategories;
        $data['categoryNamesMap'] = $categoryNamesMap;

        // Fetch non-bouquet products
        $nonBouquetProducts = [];
        if (!empty($nonBouquetCategoryIds)) {
            // Perbaiki join di sini juga jika diperlukan display kategori
            $nonBouquetProducts = $productModel->select('products.*, COALESCE(sub_categories.sub_cat_name, categories.nama_kategori) as category_display', false)
                                                ->join('sub_categories', 'sub_categories.sub_cat_id = products.sub_category_id', 'left')
                                                ->join('categories', 'categories.category_id = sub_categories.main_cat_id', 'left')
                                                ->whereIn('products.category_id', $nonBouquetCategoryIds)
                                                ->where('products.is_active', 1)
                                                ->findAll();
        }
        $data['nonBouquetProducts'] = $nonBouquetProducts;

        // Fetch bestseller bouquet products
        $bestsellerBouquetProducts = [];
        // Daftar ID produk yang Anda tentukan sebagai bestseller
        $bestsellerProductIds = [
            'PRDK003', // bucket rose 3
            'PRDK005', // bucket rose 5
            'PRDK018', // bucket rose 18
            'PRDK023', // bucket boneka 3 (sebelumnya: bucket boneko3)
            'PRDK060', // bucket mix flower 27
            'PRDK061', // bucket mix flower 28
            'PRDK082', // parsel full bunga 4
            'PRDK085', // parsel bunga + buah 3
            'PRDK111', // bunga vas keramik 15
            'PRDK114', // bunga vas keramik 18
            'PRDK116', // bunga vas kaca 2
            'PRDK137', // Bunga Papan Printing 2 x 3 (Full 2 sisi)
            'PRDK148', // Flower Box Full Bunga 1
            'PRDK151', // Flower Box Full Bunga 4
            'PRDK160'  // Flower Box Chocolate 6
        ];

        if (!empty($bestsellerProductIds)) {
            $bestsellerBouquetProducts = $productModel->select('products.*, COALESCE(sub_categories.sub_cat_name, categories.nama_kategori) as category_display', false)
                                                ->join('sub_categories', 'sub_categories.sub_cat_id = products.sub_category_id', 'left')
                                                ->join('categories', 'categories.category_id = sub_categories.main_cat_id', 'left')
                                                ->whereIn('products.product_id', $bestsellerProductIds) // Mengambil produk berdasarkan ID yang ditentukan
                                                ->where('products.is_active', 1)
                                                // Anda bisa menambahkan orderBy jika ingin urutan tertentu dari list di atas,
                                                // misalnya ->orderBy('FIELD(products.product_id, "PRDK003", "PRDK005", ...)')
                                                // Namun ini akan menjadi sangat panjang jika list ID nya banyak.
                                                // Jika tidak ada orderBy eksplisit, urutan akan mengikuti urutan database atau urutan alami dari whereIn
                                                // ->orderBy('tanggal_dibuat', 'DESC') // Hapus atau beri komentar baris ini
                                                // ->limit(6) // Hapus atau beri komentar baris ini jika Anda ingin semua bestseller yang ditentukan ditampilkan
                                                ->findAll();
        }
        $data['bestsellerBouquetProducts'] = $bestsellerBouquetProducts;
        $data['store'] = $this->storeData;
        // --- Fetch Articles ---
        $data['artikels'] = $artikelModel->orderBy('tanggal_dibuat', 'DESC')->findAll();

    // Google Reviews removed in favor of EmbedSocial widget

        // Load dashboard view with all data
        return view('dashboard', $data); // Dashboard ini yang akan menggunakan $data['store']
     
    }

    public function shop()
    {
        $productModel = new ProductModel();
        $categoryModel = new CategoryModel(); // Biarkan ini jika diperlukan

        // Ambil data filter dari request (GET atau POST)
        $keyword = $this->request->getVar('keyword');
        $categoryId = $this->request->getVar('category');

        // Mulai query builder untuk produk yang aktif
        $productsQuery = $productModel->select('products.*, COALESCE(sub_categories.sub_cat_name, categories.nama_kategori) as category_display', false)
                                    ->join('sub_categories', 'sub_categories.sub_cat_id = products.sub_category_id', 'left')
                                    ->join('categories', 'categories.category_id = sub_categories.main_cat_id', 'left')
                                    ->where('products.is_active', 1);

        // Terapkan filter jika ada
        if (!empty($categoryId)) {
            // Jika ingin filter berdasarkan sub_category_id
            $productsQuery->where('products.sub_category_id', $categoryId);
            // Atau jika ingin filter berdasarkan main_cat_id (dari categories)
            // $productsQuery->where('sub_categories.main_cat_id', $categoryId); 
        }

        if (!empty($keyword)) {
            $productsQuery->groupStart() // Mulai grup untuk OR
                          ->like('products.nama_produk', $keyword)
                          ->orLike('products.deskripsi_produk', $keyword)
                          ->groupEnd(); // Akhiri grup
        }
          $data['store'] = $this->storeData;
        // Siapkan data untuk view
        $data = [
            'products'   => $productsQuery->paginate(9, 'shop_group'), // 'shop_group' adalah nama grup paginasi
            'pager'      => $productModel->pager,
            'categories' => $categoryModel->findAll(), // Tetap ambil semua kategori utama
            'selectedCategory' => $categoryId,
            'keyword'    => $keyword,
        ];
     
        return view('shop', $data); // Dashboard ini yang akan menggunakan $data['store']
       
    }

public function productDetail($id)
{
    // 1. Ambil data produk utama
    $product = $this->productModel->getProductDetails($id);

    if (!$product) {
        throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
    }

    // 2. Buat 'category_display' untuk produk utama
    if (!empty($product['sub_cat_name'])) {
        $product['category_display'] = $product['nama_kategori'] . ' / ' . $product['sub_cat_name'];
    } else {
        $product['category_display'] = $product['nama_kategori'];
    }

    // 3. Ambil varian dan gambar tambahan
    $variants = $this->productVariantModel->getVariantsByProductId($id);
    if (empty($variants)) {
        $variants = [['name' => 'Default', 'price' => $product['harga']]];
    }
    $additionalImages = $this->productImageModel->getImagesByProductId($id);
    
    // Gunakan harga varian pertama sebagai harga dasar untuk perhitungan diskon
    $basePrice = isset($variants[0]) ? $variants[0]['price'] : $product['harga'];

    // 4. Panggil method untuk mengambil produk terkait
    $relatedProducts = $this->productModel->getRelatedProducts($product['sub_category_id'], $id);

    // 5. Siapkan data untuk view
    $data = [
        'title'             => 'Detail Produk | ' . $product['nama_produk'],
        'product'           => $product,
        'variants'          => $variants,
        'images'            => $additionalImages,
        'relatedProducts'   => $relatedProducts, // Nama variabel ini HARUS SAMA dengan di view
    ];
    
    // 6. Ambil info diskon untuk produk ini (gunakan harga varian pertama)
    $data['productDiscount'] = $this->discountRuleModel->getProductDiscount($id, $basePrice);
    
    // 7. Ambil diskon untuk related products
    $data['productDiscounts'] = $this->discountRuleModel->getProductsWithDiscounts();

    $data['store'] = $this->storeData;
    return view('shop-detail', $data);
}
// di dalam file: app/Controllers/Home.php

public function artikel($slug = null) // Diubah: Menerima slug
{
    $artikelModel = new \App\Models\ArtikelModel();

    $builder = $artikelModel->builder('artikel'); // Eksplisit menunjuk ke tabel 'artikel'
    
    // PERBAIKAN: Mengambil 'nama_kategori' dari tabel 'categories'
    // dan memberi alias 'category_name'
    $builder->select('artikel.*, categories.nama_kategori as category_name');
    
    // PERBAIKAN: Melakukan JOIN dengan tabel 'categories'
    // menggunakan 'categories.category_id' dan 'artikel.produk_terkait'
    // Pastikan nama kolom di tabel 'artikel' adalah 'produk_terkait' (bukan 'related_products')
    $builder->join('categories', 'categories.category_id = artikel.produk_terkait', 'left');
    
    // Filter berdasarkan SLUG artikel (perubahan utama)
    $builder->where('artikel.slug', $slug); 
    
    $query = $builder->get();
    $article = $query->getRowArray();

    if (!$article) {
        throw new \CodeIgniter\Exceptions\PageNotFoundException('Artikel tidak ditemukan.');
    }
    
    // Proses gambar base64 di dalam konten
    if (!empty($article['isi'])) {
        $article['isi'] = $this->_processBase64Images($article['isi']);
    }
    
    $related_products = [];
    if (!empty($article['produk_terkait']) && is_string($article['produk_terkait'])) {
        $product_ids = json_decode($article['produk_terkait'], true);
        if (is_array($product_ids) && !empty($product_ids)) {
            $productModel = new \App\Models\ProductModel();
            $related_products = $productModel->whereIn('product_id', $product_ids)->findAll();
        }
    }

    $data = [
        'title'           => $article['judul'],
        'artikel'         => $article, // Menggunakan kunci 'artikel' agar cocok dengan view
        'relatedProducts' => $related_products,
        'store'           => $this->storeData
    ];

    return view('artikel_detail', $data);
}
 public function allArticles()
    {
        $artikelModel = new ArtikelModel();
        $data['artikels'] = $artikelModel->orderBy('tanggal_dibuat', 'DESC')->findAll();
        $data['store'] = $this->storeData;
    
        return view('all_articles', $data);
    }

    private function _processBase64Images($content)
    {
        $dom = new \DOMDocument();
        // Use @ to suppress warnings from malformed HTML from Summernote
        @$dom->loadHTML(mb_convert_encoding($content, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $images = $dom->getElementsByTagName('img');

        foreach ($images as $img) {
            $src = $img->getAttribute('src');

            // Check if the src is a base64 string
            if (strpos($src, 'data:image/') === 0) {
                // Get the image data
                list($type, $data) = explode(';', $src);
                list(, $data)      = explode(',', $data);
                $data = base64_decode($data);

                // Get the image type (e.g., png, jpeg)
                list(, $imageType) = explode('/', $type);
                
                // Define the upload path and create it if it doesn't exist
                $uploadPath = FCPATH . 'public/uploads/artikel_images/';
                if (!is_dir($uploadPath)) {
                    mkdir($uploadPath, 0777, true);
                }

                // Create a unique filename
                $filename = uniqid('artikel_') . '.' . $imageType;
                $filepath = $uploadPath . $filename;

                // Save the file
                file_put_contents($filepath, $data);

                // Replace the src attribute with the new URL
                $newSrc = base_url('public/uploads/artikel_images/' . $filename);
                $img->setAttribute('src', $newSrc);
            }
        }

        return $dom->saveHTML();
    }
    
    /**
     * Debug method for event domain filtering
     * Temporary endpoint - remove after debugging
     */
    public function debugEventDomain()
    {
        $eventBannerModel = new EventBannerModel();
        $currentDomain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        
        $debug = $eventBannerModel->debugDomainFiltering($currentDomain);
        $activeEvents = $eventBannerModel->getActiveEventBanners($currentDomain);
        
        echo "<h2>Event Domain Debug</h2>";
        echo "<h3>Current Domain: " . htmlspecialchars($currentDomain) . "</h3>";
        echo "<h3>Active Events Count: " . count($activeEvents) . "</h3>";
        
        echo "<h3>Debug Details:</h3>";
        echo "<pre>";
        print_r($debug);
        echo "</pre>";
        
        echo "<h3>Final Filtered Events:</h3>";
        echo "<pre>";
        foreach ($activeEvents as $event) {
            echo "ID: " . $event['id'] . " - " . $event['title'] . "\n";
        }
        echo "</pre>";
        
        echo "<p><a href='" . base_url() . "'>Back to Home</a></p>";
    }

    /**
     * Public Return Policy page for Google Merchant Center
     */
    public function returnPolicy()
    {
        $data = [
            'store' => $this->storeData,
        ];
        return view('return_policy', $data);
    }
}
