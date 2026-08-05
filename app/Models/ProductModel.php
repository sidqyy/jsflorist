<?php

namespace App\Models;

use CodeIgniter\Model;

class ProductModel extends Model
{
    protected $table            = 'products';
    protected $primaryKey       = 'product_id';

    protected $useAutoIncrement = false;

    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;

    protected $allowedFields = [
        'product_id',
        'sub_category_id',
        'nama_produk',
        'deskripsi_produk',
        'harga',
        'gambar_url',
        'is_active',
    ];

    // Kita set ke false karena kita akan menangani timestamp di fungsi custom save
    protected $useTimestamps = false;
    
    protected $createdField  = 'tanggal_dibuat';
    protected $updatedField  = 'tanggal_diupdate';
    protected $dateFormat    = 'datetime';

    protected $validationRules = [
        'sub_category_id'    => 'required',
        'nama_produk'        => 'required|min_length[5]|max_length[255]',
        'deskripsi_produk'   => 'permit_empty',
        'harga'              => 'required|decimal|greater_than_equal_to[0]',
        'gambar_url'         => 'permit_empty',
        'is_active'          => 'in_list[0,1]',
    ];

    protected $validationMessages = [];
    protected $skipValidation     = false;

    /**
     * FUNGSI BARU: Solusi final untuk menyimpan produk menggunakan Query Builder.
     */
    public function saveProduct($data)
    {
        // Tambahkan timestamp secara manual di sini
        $data['tanggal_dibuat'] = date('Y-m-d H:i:s');
        $data['tanggal_diupdate'] = date('Y-m-d H:i:s');
        
        // Gunakan query builder untuk insert
        return $this->db->table($this->table)->insert($data);
    }
  public function getNextProductId()
    {
        // 1. Definisikan query SQL murni untuk mencari ID numerik tertinggi.
        $sql = "SELECT product_id FROM products
                WHERE product_id LIKE 'PRDK%'
                  AND product_id REGEXP '^PRDK[0-9]+$'
                ORDER BY CAST(SUBSTRING(product_id, 5) AS UNSIGNED) DESC
                LIMIT 1";

        // 2. Eksekusi query menggunakan koneksi database.
        $query = $this->db->query($sql);
        $result = $query->getRow();

        if ($result) {
            // 3. Jika ID ditemukan, ambil nomornya dari string dan tambahkan 1.
            $lastIdNumber = (int) substr($result->product_id, 4);
            $nextIdNumber = $lastIdNumber + 1;
        } else {
            // 4. Jika tidak ada ID produk numerik, mulai dari 1.
            $nextIdNumber = 1;
        }

        // 5. Format ID baru dengan padding nol.
        return 'PRDK' . str_pad($nextIdNumber, 3, '0', STR_PAD_LEFT);
    }

     public function getProductDetails($id)
    {
        return $this->select('
                products.*, 
                c.nama_kategori, 
                sc.sub_cat_name
            ')
            ->join('sub_categories sc', 'sc.sub_cat_id = products.sub_category_id', 'left')
            ->join('categories c', 'c.category_id = sc.main_cat_id OR c.category_id = products.sub_category_id', 'left')
            ->where('products.product_id', $id)
            ->groupBy('products.product_id')
            ->first();
    }
    
     public function getRelatedProducts($subCategoryId, $currentProductId)
    {
        if (empty($subCategoryId)) {
            return [];
        }

        $products = $this->select('
                        products.product_id,
                        products.nama_produk,
                        products.harga,
                        products.deskripsi_produk,
                        products.gambar_url,
                        c.nama_kategori,
                        sc.sub_cat_name
                    ')
                    ->join('sub_categories sc', 'sc.sub_cat_id = products.sub_category_id', 'left')
                    ->join('categories c', 'c.category_id = sc.main_cat_id OR c.category_id = products.sub_category_id', 'left')
                    ->where('products.sub_category_id', $subCategoryId)
                    ->where('products.product_id !=', $currentProductId)
                    ->groupBy('products.product_id')
                    ->limit(4)
                    ->findAll();

        foreach ($products as &$product) {
            if (!empty($product['sub_cat_name'])) {
                $product['category_display'] = $product['nama_kategori'] . ' / ' . $product['sub_cat_name'];
            } else {
                $product['category_display'] = $product['nama_kategori'];
            }
        }

        return $products;
    }
    
    public function getAllProductsFiltered($filters = [])
    {
        $builder = $this->select('
                products.*, 
                c.category_name, 
                sc.sub_category_name
            ')
            ->join('sub_categories sc', 'sc.sub_cat_id = products.sub_category_id', 'left')
            ->join('categories c', 'c.category_id = sc.category_id OR c.category_id = products.sub_category_id', 'left');

        if (!empty($filters['category'])) {
            $builder->where('products.sub_category_id', $filters['category']);
        }
        
        if (!empty($filters['keyword'])) {
             $builder->like('products.name', $filters['keyword']);
        }
        
        return $builder->groupBy('products.product_id');
    }

    public function getProductsWithVariantPriceRange($filters = [])
    {
        $builder = $this->select('
                products.*,
                c.nama_kategori AS category_name,
                MIN(pv.price) AS min_price,
                MAX(pv.price) AS max_price
            ')
            ->join('categories c', 'c.category_id = products.sub_category_id', 'left')
            ->join('product_variants pv', 'pv.product_id = products.product_id', 'left')
            ->groupBy('products.product_id');

        if (!empty($filters['category'])) {
            $builder->where('products.sub_category_id', $filters['category']);
        }

        if (!empty($filters['keyword'])) {
            $builder->like('products.nama_produk', $filters['keyword']);
        }

        return $builder;
    }
}
