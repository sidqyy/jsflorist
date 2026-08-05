<?php

namespace App\Models;

use CodeIgniter\Model;

class SubCategoryModel extends Model
{
    protected $table = 'sub_categories';
    protected $primaryKey = 'sub_cat_id';

    public function getSubcategoriesByMainCatId($mainCatId)
    {
        return $this->where('main_cat_id', $mainCatId)->findAll();
    }
}