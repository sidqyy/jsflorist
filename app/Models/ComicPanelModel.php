<?php

namespace App\Models;

use CodeIgniter\Model;

class ComicPanelModel extends Model
{
    protected $table = 'comic_panels';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'episode_id',
        'panel_number',
        'image_path',
        'caption',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
