<?php

namespace App\Models;

use CodeIgniter\Model;

class ComicEpisodeModel extends Model
{
    protected $table = 'comic_episodes';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'episode_number',
        'title',
        'slug',
        'description',
        'cover_image',
        'is_active',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';
}
