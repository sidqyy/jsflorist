<?php

namespace App\Models;

use CodeIgniter\Model;

class CategoryModel extends Model
{
    protected $table      = 'categories';
    protected $primaryKey = 'category_id';

    protected $useAutoIncrement = true;

    protected $returnType     = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = ['nama_kategori', 'deskripsi'];

    protected $useTimestamps = false; // Tidak ada kolom timestamp di tabel categories
    protected $dateFormat    = 'datetime';

    protected $validationRules = [
        'nama_kategori' => 'required|min_length[3]|max_length[100]|is_unique[categories.nama_kategori]',
    ];

    protected $validationMessages = [
        'nama_kategori' => [
            'is_unique' => 'Nama kategori ini sudah ada.',
            'required' => 'Nama kategori wajib diisi.',
            'min_length' => 'Nama kategori minimal 3 karakter.'
        ]
    ];

    protected $skipValidation = false;

    public function getCategoriesWithSubcategories()
    {
        $subCategoryModel = new SubCategoryModel();
        $categories = $this->findAll();

        foreach ($categories as &$category) {
            $category['sub_categories'] = $subCategoryModel->getSubcategoriesByMainCatId($category['category_id']);
        }

        return $categories;
    }
}
