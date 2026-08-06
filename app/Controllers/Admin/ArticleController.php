<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ArtikelModel;
use App\Models\ProductModel;
use App\Models\CategoryModel;
use App\Models\SubCategoryModel;


class ArticleController extends BaseController
{
    protected $artikelModel;
    protected $productModel;
    protected $categoryModel;

    public function __construct()
    {
        $this->artikelModel = new ArtikelModel();
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
        helper(['image']);
    }


    public function index()
    {
        $data = [
            'articles' => $this->artikelModel->orderBy('tanggal_dibuat', 'DESC')->paginate(10, 'articles'),
            'pager' => $this->artikelModel->pager,
        ];
        return view('admin/articles/index', $data);
    }

    public function create()
    {
        $data = [
            'products' => $this->productModel->where('is_active', 1)->findAll(),
            'categories' => $this->categoryModel->findAll(), // Mengambil semua kategori utama
        ];
        return view('admin/articles/create', $data);
    }


     public function store()
    {
        $rules = [
            'judul' => 'required|min_length[5]',
            'isi' => 'required|min_length[20]',
            'gambar' => 'uploaded[gambar]|max_size[gambar,2048]|is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $gambarFile = $this->request->getFile('gambar');
        $namaGambar = upload_and_convert_to_webp($gambarFile, FCPATH . 'assets/img/artikel');

        if ($namaGambar) {
            // Ambil produk terkait dari input array, lalu encode ke JSON
            $produkTerkait = $this->request->getVar('produk_terkait');
            $produkTerkaitJson = !empty($produkTerkait) ? json_encode($produkTerkait) : null;
            
            $judulArtikel = $this->request->getVar('judul');
            $slug = url_title($judulArtikel, '-', TRUE); // Ditambahkan: Buat slug dari judul
            
            // Ditambahkan: Pastikan slug unik
            $i = 0;
            $originalSlug = $slug;
            while ($this->artikelModel->where('slug', $slug)->first()) {
                $i++;
                $slug = $originalSlug . '-' . $i;
            }

            $data = [
                'judul' => $judulArtikel, // Gunakan variabel judulArtikel yang sudah ada
                'slug' => $slug, // Ditambahkan: Simpan slug
                'isi' => $this->request->getVar('isi'),
                'gambar' => $namaGambar,
                'produk_terkait' => $produkTerkaitJson,
            ];

            if ($this->artikelModel->save($data)) {
                return redirect()->to('/admin/articles')->with('success', 'Artikel berhasil ditambahkan.');
            } else {
                return redirect()->back()->withInput()->with('errors', ['Gagal menyimpan artikel.']);
            }
        } else {
            return redirect()->back()->withInput()->with('errors', ['Gagal mengunggah gambar.']);
        }
    }


    public function edit($id)
    {
        $data = [
            'article' => $this->artikelModel->find($id),
            'products' => $this->productModel->where('is_active', 1)->findAll(),
            'categories' => $this->categoryModel->getCategoriesWithSubcategories(),
        ];


        if (empty($data['article'])) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Artikel tidak ditemukan.');
        }

        $data['selected_products'] = json_decode($data['article']['produk_terkait'], true) ?? [];

        return view('admin/articles/edit', $data);
    }

  public function update($id)
    {
        $rules = [
            'judul' => 'required|min_length[5]',
            'isi' => 'required|min_length[20]',
        ];

        $gambarFile = $this->request->getFile('gambar');
        if ($gambarFile && $gambarFile->isValid()) {
            $rules['gambar'] = 'max_size[gambar,2048]|is_image[gambar]|mime_in[gambar,image/jpg,image/jpeg,image/png,image/webp]';
        }

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $judulArtikel = $this->request->getVar('judul'); // Ditambahkan: Ambil judul baru
        $currentArticle = $this->artikelModel->find($id); // Ditambahkan: Ambil artikel saat ini untuk slug lama

        $newSlug = url_title($judulArtikel, '-', TRUE); // Ditambahkan: Buat slug baru

        // Ditambahkan: Cek keunikan slug baru hanya jika berubah
        if ($newSlug !== $currentArticle['slug']) {
            $i = 0;
            $originalNewSlug = $newSlug;
            while ($this->artikelModel->where('slug', $newSlug)->where('id_artikel !=', $id)->first()) {
                $i++;
                $newSlug = $originalNewSlug . '-' . $i;
            }
        } else {
            $newSlug = $currentArticle['slug']; // Jika judul tidak berubah, pertahankan slug lama
        }

        $data = [
            'judul' => $judulArtikel, // Gunakan variabel judulArtikel
            'slug' => $newSlug, // Ditambahkan: Update slug
            'isi' => $this->request->getVar('isi'),
            'produk_terkait' => json_encode($this->request->getVar('produk_terkait')),
        ];

        if ($gambarFile && $gambarFile->isValid()) {
            // Hapus gambar lama jika ada
            $artikelLama = $this->artikelModel->find($id);
            if ($artikelLama['gambar'] && file_exists('assets/img/artikel/' . $artikelLama['gambar'])) {
                unlink('assets/img/artikel/' . $artikelLama['gambar']);
            }
            // Pindahkan gambar baru
            $namaGambar = upload_and_convert_to_webp($gambarFile, FCPATH . 'assets/img/artikel');
            if ($namaGambar) {
                $data['gambar'] = $namaGambar;
            }
        }

        if ($this->artikelModel->update($id, $data)) {
            return redirect()->to('/admin/articles')->with('success', 'Artikel berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('errors', ['Gagal memperbarui artikel.']);
        }
    }


    public function delete($id)
    {
        $artikel = $this->artikelModel->find($id);
        if ($artikel) {
            // Hapus gambar dari server
            if ($artikel['gambar'] && file_exists('assets/img/artikel/' . $artikel['gambar'])) {
                unlink('assets/img/artikel/' . $artikel['gambar']);
            }
            $this->artikelModel->delete($id);
            return redirect()->to('/admin/articles')->with('success', 'Artikel berhasil dihapus.');
        }
        return redirect()->to('/admin/articles')->with('error', 'Artikel tidak ditemukan.');
    }

    public function getProductsByCategory()
    {
        if (!$this->request->isAJAX()) {
            return redirect()->to('/admin');
        }

        $categoryId = $this->request->getPost('category');
        $products = [];
        $subCategoryModel = new SubCategoryModel();

        if ($categoryId) {
            $subCategories = $subCategoryModel->where('main_cat_id', $categoryId)->findAll();
            $subCategoryIds = array_column($subCategories, 'sub_cat_id');
            $subCategoryIds[] = $categoryId; // Tambahkan kategori utama ke dalam daftar

            $products = $this->productModel
                            ->select('product_id, nama_produk')
                            ->whereIn('sub_category_id', $subCategoryIds)
                            ->where('is_active', 1)
                            ->findAll();
        } else {
            // If no category is selected, return all active products
            $products = $this->productModel
                            ->select('product_id, nama_produk')
                            ->where('is_active', 1)
                            ->findAll();
        }

        return $this->response->setJSON($products);
    }
}