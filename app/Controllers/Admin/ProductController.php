<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\SubCategoryModel;
use App\Models\OccasionModel;
use App\Models\ProductOccasionModel;
use App\Models\ProductVariantModel;
use App\Models\ProductImageModel;
use App\Models\ProductComponentModel;
use Exception;

class ProductController extends BaseController
{
    protected $productModel;
    protected $categoryModel;
    protected $subCategoryModel;
    protected $occasionModel;
    protected $productOccasionModel;
    protected $productVariantModel;
    protected $productImageModel;
    protected $productComponentModel;

    public function __construct()
    {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        $this->subCategoryModel = new SubCategoryModel();
        $this->occasionModel = new OccasionModel();
        $this->productOccasionModel = new ProductOccasionModel();
        $this->productVariantModel = new ProductVariantModel();
        $this->productImageModel = new ProductImageModel();
        $this->productComponentModel = new ProductComponentModel();
        helper(['form', 'url']);
    }

    public function index()
    {
        $categoryId = $this->request->getGet('category');
        $perPage = 10;
        
        $builder = $this->productModel
            ->select('products.*,
                      sub_categories.sub_cat_name AS sub_category_name,
                      categories.nama_kategori AS main_category_name,
                      direct_categories.nama_kategori AS direct_main_category_name')
            ->join('sub_categories', 'sub_categories.sub_cat_id = products.sub_category_id', 'left')
            ->join('categories', 'categories.category_id = sub_categories.main_cat_id', 'left')
            ->join('categories AS direct_categories', 'direct_categories.category_id = products.sub_category_id', 'left');

        if (!empty($categoryId)) {
            $subCategoryIds = $this->subCategoryModel->where('main_cat_id', $categoryId)->findColumn('sub_cat_id');
            
            if (!empty($subCategoryIds)) {
                $builder->whereIn('products.sub_category_id', $subCategoryIds);
            } else {
                $builder->where('products.sub_category_id', $categoryId);
            }
        }

        $products = $builder->paginate($perPage, 'products');

        return view('admin/products/index', [
            'products' => $products,
            'pager' => $this->productModel->pager,
            'categories' => $this->categoryModel->findAll(),
            'selectedCategory' => $categoryId
        ]);
    }
    
    public function create()
    {
        $subcategories = $this->subCategoryModel->findAll();
        $categories = $this->categoryModel->findAll();
        $combinedList = [];
        
        foreach ($categories as $category) {
            $hasSubcategory = false;
            foreach ($subcategories as $sub) {
                if ($sub['main_cat_id'] == $category['category_id']) {
                    $hasSubcategory = true;
                    break;
                }
            }
            if ($hasSubcategory) {
                $combinedList[] = [
                    'id' => $category['category_id'],
                    'name' => $category['nama_kategori'] . ' (Kategori Utama)',
                ];
                foreach ($subcategories as $sub) {
                    if ($sub['main_cat_id'] == $category['category_id']) {
                        $combinedList[] = [
                            'id' => $sub['sub_cat_id'],
                            'name' => '--- ' . $sub['sub_cat_name'],
                        ];
                    }
                }
            } else {
                 $combinedList[] = [
                    'id' => $category['category_id'],
                    'name' => $category['nama_kategori'],
                ];
            }
        }

        $data['categories'] = $combinedList;
        $data['occasions'] = $this->occasionModel->findAll();
        return view('admin/products/create', $data);
    }

    public function getSubcategoriesByCategoryId($categoryId)
    {
        $subcategories = $this->subCategoryModel
            ->where('main_cat_id', $categoryId)
            ->findAll();
        return $this->response->setJSON($subcategories);
    }

    public function edit($id)
    {
        $productData = $this->productModel->find($id);

        if (!$productData) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound();
        }

        $productSubCategoryId = $productData['sub_category_id'];
        $subcategoryCheck = $this->subCategoryModel->find($productSubCategoryId);

        $selectedCategoryId = null;
        $selectedSubcategoryId = null;

        if ($subcategoryCheck) {
            $selectedSubcategoryId = $productSubCategoryId;
            $selectedCategoryId = $subcategoryCheck['main_cat_id'];
        } else {
            $selectedCategoryId = $productSubCategoryId;
        }
        
        $productOccasions = $this->productOccasionModel->where('product_id', $id)->findAll();
        $product_occasion_ids = array_column($productOccasions, 'occasion_id');

        $data = [
            'title'                 => 'Edit Produk',
            'product'               => $productData,
            'categories'            => $this->categoryModel->findAll(),
            'selectedCategoryId'    => $selectedCategoryId,
            'selectedSubcategoryId' => $selectedSubcategoryId,
            'occasions'             => $this->occasionModel->findAll(),
            'product_occasion_ids'  => $product_occasion_ids,
            'variants'              => $this->productVariantModel->getVariantsByProductId($id),
            'images'                => $this->productImageModel->getImagesByProductId($id),
            'components'            => $this->productComponentModel->where('product_id', $id)->orderBy('sort_order', 'ASC')->findAll(),
        ];

        return view('admin/products/edit', $data);
    }

    public function update($id)
    {
        $rules = [
            'nama_produk' => 'required|min_length[3]',
            'harga'       => 'required|numeric',
        ];

        $subCategoryId = $this->request->getVar('subcategory_id');
        if (!empty($subCategoryId)) {
            $rules['subcategory_id'] = 'required';
            $rules['category_id'] = 'required';
        } else {
            $rules['category_id'] = 'required';
        }
        
        $gambarFile = $this->request->getFile('gambar_url');
        if ($gambarFile && $gambarFile->isValid() && !$gambarFile->hasMoved()) {
            if ($gambarFile->getSize() > 2097152) { 
                return redirect()->back()->withInput()->with('error', 'Ukuran gambar utama tidak boleh lebih dari 2MB. Ukuran file Anda: ' . round($gambarFile->getSize() / 1024 / 1024, 2) . 'MB');
            }
            $rules['gambar_url'] = 'max_size[gambar_url,2048]|is_image[gambar_url]|mime_in[gambar_url,image/jpg,image/jpeg,image/png]';
        }

        $variantsData = $this->request->getPost('variants');
        if (!empty($variantsData)) {
            foreach ($variantsData as $key => $variant) {
                $variantImageFile = $this->request->getFile('variants.' . $key . '.gambar_varian_url');
                if ($variantImageFile && $variantImageFile->isValid() && !$variantImageFile->hasMoved()) {
                    if ($variantImageFile->getSize() > 2097152) { 
                        return redirect()->back()->withInput()->with('error', 'Ukuran gambar varian tidak boleh lebih dari 2MB. Ukuran file varian ' . ($key + 1) . ': ' . round($variantImageFile->getSize() / 1024 / 1024, 2) . 'MB');
                    }
                    $rules["variants.{$key}.gambar_varian_url"] = 'max_size[variants.' . $key . '.gambar_varian_url,2048]|is_image[variants.' . $key . '.gambar_varian_url]|mime_in[variants.' . $key . '.gambar_varian_url,image/jpg,image/jpeg,image/png]';
                }
            }
        }

        $allFiles = $this->request->getFiles();
        if (isset($allFiles['additional_images']) && is_array($allFiles['additional_images'])) {
            foreach ($allFiles['additional_images'] as $index => $img) {
                if ($img && $img->isValid() && !$img->hasMoved()) {
                    if ($img->getSize() > 2097152) { 
                        return redirect()->back()->withInput()->with('error', 'Ukuran gambar tambahan tidak boleh lebih dari 2MB. Ukuran file gambar ' . ($index + 1) . ': ' . round($img->getSize() / 1024 / 1024, 2) . 'MB');
                    }
                    $rules["additional_images.{$index}"] = 'max_size[additional_images.' . $index . ',2048]|is_image[additional_images.' . $index . ']|mime_in[additional_images.' . $index . ',image/jpg,image/jpeg,image/png]';
                }
            }
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $categoryId = $this->request->getVar('category_id');
        $finalCategoryId = !empty($subCategoryId) ? $subCategoryId : $categoryId;
        
        $dataToUpdate = [
            'nama_produk'      => $this->request->getVar('nama_produk'),
            'deskripsi_produk' => $this->request->getVar('deskripsi_produk'),
            'harga'            => $this->request->getVar('harga'),
            'sub_category_id'  => $finalCategoryId,
            'is_active'        => $this->request->getVar('is_active') ?? 0,
        ];

        if ($gambarFile && $gambarFile->isValid() && !$gambarFile->hasMoved()) {
            $productLama = $this->productModel->find($id);
            if ($productLama && !empty($productLama['gambar_url']) && file_exists(FCPATH . 'assets/img/gambar/' . $productLama['gambar_url'])) {
                unlink(FCPATH . 'assets/img/gambar/' . $productLama['gambar_url']);
            }
            $namaGambar = $gambarFile->getRandomName();
            $gambarFile->move(FCPATH . 'assets/img/gambar', $namaGambar);
            $dataToUpdate['gambar_url'] = $namaGambar;
        }

        if ($this->productModel->update($id, $dataToUpdate)) {
            $this->productOccasionModel->where('product_id', $id)->delete();
            $occasions = $this->request->getVar('occasions');
            if (!empty($occasions)) {
                foreach ($occasions as $occasionId) {
                    $this->productOccasionModel->insert([
                        'product_id'  => $id,
                        'occasion_id' => $occasionId,
                    ]);
                }
            }

            $existingVariantIds = array_column($this->productVariantModel->where('product_id', $id)->findAll(), 'id');
            $updatedVariantIds = [];

            if (!empty($variantsData)) {
                foreach ($variantsData as $key => $variantInput) {
                    $variantId = $variantInput['id'];
                    $variantName = $variantInput['name'];
                    $variantPrice = str_replace('.', '', $variantInput['price'] ?? '0');
                    $variantPrice = str_replace(',', '.', $variantPrice);
                    $variantPrice = (float) $variantPrice;

                    $variantDataToSave = [
                        'product_id' => $id,
                        'name'       => $variantName,
                        'price'      => $variantPrice,
                    ];

                    $variantImageFile = $this->request->getFile('variants.' . $key . '.gambar_varian_url');
                    if ($variantImageFile && $variantImageFile->isValid() && !$variantImageFile->hasMoved()) {
                        $newVariantImageName = $variantImageFile->getRandomName();
                        if (!is_dir(FCPATH . 'assets/img/variants')) {
                            mkdir(FCPATH . 'assets/img/variants', 0777, true);
                        }
                        $variantImageFile->move(FCPATH . 'assets/img/variants', $newVariantImageName);
                        $variantDataToSave['gambar_varian_url'] = $newVariantImageName;

                        if ($variantId !== 'new') {
                            $oldVariant = $this->productVariantModel->find($variantId);
                            if ($oldVariant && !empty($oldVariant['gambar_varian_url']) && file_exists(FCPATH . 'assets/img/variants/' . $oldVariant['gambar_varian_url'])) {
                                unlink(FCPATH . 'assets/img/variants/' . $oldVariant['gambar_varian_url']);
                            }
                        }
                    } elseif ($variantId !== 'new') {
                        $existingVariantImage = $variantInput['existing_gambar_varian_url'] ?? null;
                        $variantDataToSave['gambar_varian_url'] = $existingVariantImage;
                    } else {
                        $variantDataToSave['gambar_varian_url'] = null;
                    }

                    if ($variantId === 'new') {
                        $this->productVariantModel->insert($variantDataToSave);
                        $updatedVariantIds[] = $this->productVariantModel->getInsertID();
                    } else {
                        $this->productVariantModel->update($variantId, $variantDataToSave);
                        $updatedVariantIds[] = $variantId;
                    }
                }
            }

            $variantsToDelete = array_diff($existingVariantIds, $updatedVariantIds);
            foreach ($variantsToDelete as $variantToDeleteId) {
                $variantToDelete = $this->productVariantModel->find($variantToDeleteId);
                if ($variantToDelete && !empty($variantToDelete['gambar_varian_url']) && file_exists(FCPATH . 'assets/img/variants/' . $variantToDelete['gambar_varian_url'])) {
                    unlink(FCPATH . 'assets/img/variants/' . $variantToDelete['gambar_varian_url']);
                }
                $this->productVariantModel->delete($variantToDeleteId);
            }

            $allFiles = $this->request->getFiles();
            if (isset($allFiles['additional_images']) && is_array($allFiles['additional_images'])) {
                foreach ($allFiles['additional_images'] as $img) {
                    if ($img && $img->isValid() && !$img->hasMoved()) {
                        $imageName = $img->getRandomName();
                        $img->move(FCPATH . 'assets/img/products', $imageName);
                        $this->productImageModel->insert([
                            'product_id' => $id,
                            'image_url'  => $imageName,
                        ]);
                    }
                }
            }

            $deletedImages = $this->request->getPost('delete_images');
            if ($deletedImages) {
                foreach($deletedImages as $imageId => $value) {
                    $image = $this->productImageModel->find($imageId);
                    if($image && !empty($image['image_url']) && file_exists(FCPATH . 'assets/img/products/' . $image['image_url'])) {
                        unlink(FCPATH . 'assets/img/products/' . $image['image_url']);
                    }
                    $this->productImageModel->delete($imageId);
                }
            }

            $existingComponentIds = array_column($this->productComponentModel->where('product_id', $id)->findAll(), 'id');
            $updatedComponentIds = [];
            $componentsData = $this->request->getPost('components');
            if (!empty($componentsData) && is_array($componentsData)) {
                foreach ($componentsData as $comp) {
                    $compId = $comp['id'] ?? 'new';
                    $name = trim($comp['component_name'] ?? '');
                    if ($name === '') { continue; }
                    $qty = (float) str_replace(',', '.', (string) ($comp['quantity'] ?? 0));
                    $unitCost = (float) str_replace(',', '.', (string) ($comp['unit_cost'] ?? 0));
                    $sortOrder = (int) ($comp['sort_order'] ?? 0);

                    $payload = [
                        'product_id'     => $id,
                        'component_name' => $name,
                        'quantity'       => $qty,
                        'unit_cost'      => $unitCost,
                        'sort_order'     => $sortOrder,
                    ];

                    if ($compId === 'new' || $compId === '' || $compId === null) {
                        $this->productComponentModel->insert($payload);
                        $updatedComponentIds[] = (int) $this->productComponentModel->getInsertID();
                    } else {
                        $this->productComponentModel->update($compId, $payload);
                        $updatedComponentIds[] = (int) $compId;
                    }
                }
            }
            $componentsToDelete = array_diff($existingComponentIds, $updatedComponentIds);
            foreach ($componentsToDelete as $cid) {
                $this->productComponentModel->delete($cid);
            }

            // ==========================================================
            // LOGIKA BARU: REDIRECT DENGAN QUERY STRING ASAL (PAGINATION)
            // ==========================================================
            $referer = $this->request->getServer('HTTP_REFERER');
            $queryString = '';

            if ($referer) {
                $parsedUrl = parse_url($referer);
                if (!empty($parsedUrl['query'])) {
                    $queryString = '?' . $parsedUrl['query'];
                }
            }

            return redirect()->to('/admin/products' . $queryString)->with('success', 'Produk berhasil diperbarui.');
            // ==========================================================

        } else {
            return redirect()->back()->withInput()->with('errors', ['Gagal memperbarui data di database.']);
        }
    }

    public function delete($id)
    {
        $product = $this->productModel->find($id);
        if ($product) {
            $this->productOccasionModel->where('product_id', $id)->delete();

            if ($product['gambar_url'] && file_exists('assets/img/gambar/' . $product['gambar_url'])) {
                unlink('assets/img/gambar/' . $product['gambar_url']);
            }
            $this->productModel->delete($id);
            return redirect()->to('/admin/products')->with('success', 'Produk berhasil dihapus.');
        }
        return redirect()->to('/admin/products')->with('error', 'Produk tidak ditemukan.');
    }
    
    public function store()
    {
        try {
            log_message('debug', 'ProductController store() method called');
            
            log_message('debug', 'POST data: ' . json_encode($this->request->getPost()));
            log_message('debug', 'FILES data: ' . json_encode($_FILES));
            
            $gambarFile = $this->request->getFile('gambar_url');
            
            if (!$gambarFile || !$gambarFile->isValid()) {
                log_message('debug', 'File validation failed - no valid file uploaded');
                return redirect()->back()->withInput()->with('error', 'Gambar produk wajib diupload dan harus valid.');
            }
            
            if ($gambarFile->hasMoved()) {
                log_message('debug', 'File validation failed - file already moved');
                return redirect()->back()->withInput()->with('error', 'Terjadi error saat memproses gambar.');
            }
            
            $fileSize = $gambarFile->getSize();
            log_message('debug', 'File size uploaded: ' . $fileSize . ' bytes (' . round($fileSize / 1024 / 1024, 2) . 'MB)');
            
            if ($fileSize > 2097152) { 
                log_message('debug', 'File size validation failed - redirecting back');
                $errorMsg = 'Ukuran gambar utama tidak boleh lebih dari 2MB. Ukuran file Anda: ' . round($fileSize / 1024 / 1024, 2) . 'MB';
                return redirect()->back()->withInput()->with('error', $errorMsg);
            }

            $allFiles = $this->request->getFiles();
            if (isset($allFiles['additional_images']) && is_array($allFiles['additional_images'])) {
                foreach ($allFiles['additional_images'] as $index => $img) {
                    if ($img && $img->isValid() && !$img->hasMoved()) {
                        if ($img->getSize() > 2097152) { 
                            log_message('debug', 'Additional image size validation failed for image ' . ($index + 1));
                            return redirect()->back()->withInput()->with('error', 'Ukuran gambar tambahan tidak boleh lebih dari 2MB. Ukuran file gambar ' . ($index + 1) . ': ' . round($img->getSize() / 1024 / 1024, 2) . 'MB');
                        }
                    }
                }
            }

            $rules = [
                'nama_produk'      => 'required|min_length[3]',
                'harga'            => 'required|numeric',
                'gambar_url'       => 'uploaded[gambar_url]|max_size[gambar_url,2048]|is_image[gambar_url]|mime_in[gambar_url,image/jpg,image/jpeg,image/png]',
                'sub_category_id'  => 'required',
            ];

            if (!$this->validate($rules)) {
                log_message('debug', 'Form validation failed: ' . json_encode($this->validator->getErrors()));
                return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
            }

            log_message('debug', 'All validations passed, proceeding with file upload');
            
            if (!$gambarFile->isValid() || $gambarFile->hasMoved()) {
                return redirect()->back()->withInput()->with('errors', ['gambar_url' => $gambarFile->getErrorString()]);
            }

            $namaGambar = $gambarFile->getRandomName();
            $gambarFile->move('assets/img/gambar', $namaGambar);
                
            $newProductId = $this->productModel->getNextProductId(); 
            
            $data = [
                'product_id'       => $newProductId,
                'nama_produk'      => $this->request->getVar('nama_produk'),
                'deskripsi_produk' => $this->request->getVar('deskripsi_produk'),
                'harga'            => $this->request->getVar('harga'),
                'sub_category_id'  => $this->request->getVar('sub_category_id'),
                'is_active'        => $this->request->getVar('is_active') ?? 0,
                'gambar_url'       => $namaGambar,
            ];

            if ($this->productModel->save($data)) {
                $occasions = $this->request->getVar('occasions');
                if (!empty($occasions)) {
                    foreach ($occasions as $occasionId) {
                        $this->productOccasionModel->insert([
                            'product_id'  => $newProductId,
                            'occasion_id' => $occasionId,
                        ]);
                    }
                }

                $componentsData = $this->request->getPost('components');
                if (!empty($componentsData) && is_array($componentsData)) {
                    foreach ($componentsData as $comp) {
                        $name = trim($comp['component_name'] ?? '');
                        if ($name === '') { continue; }
                        $qty = (float) str_replace(',', '.', (string) ($comp['quantity'] ?? 0));
                        $unitCost = (float) str_replace(',', '.', (string) ($comp['unit_cost'] ?? 0));
                        $sortOrder = (int) ($comp['sort_order'] ?? 0);

                        if ($qty < 0) { $qty = 0; }
                        if ($unitCost < 0) { $unitCost = 0; }

                        $this->productComponentModel->insert([
                            'product_id'     => $newProductId,
                            'component_name' => $name,
                            'quantity'       => $qty,
                            'unit_cost'      => $unitCost,
                            'sort_order'     => $sortOrder,
                        ]);
                    }
                }

                return redirect()->to('/admin/products')->with('success', 'Produk berhasil ditambahkan.');

            } else {
                log_message('debug', 'Failed to save product to database');
                return redirect()->back()->withInput()->with('errors', ['db_error' => 'Gagal menyimpan produk ke database.']);
            }
            
        } catch (Exception $e) {
            log_message('error', 'Exception in ProductController store(): ' . $e->getMessage());
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}