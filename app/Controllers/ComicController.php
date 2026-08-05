<?php

namespace App\Controllers;

use App\Models\ComicEpisodeModel;
use App\Models\ComicPanelModel;

class ComicController extends BaseController
{
    protected $episodeModel;
    protected $panelModel;

    public function __construct()
    {
        $this->episodeModel = new ComicEpisodeModel();
        $this->panelModel = new ComicPanelModel();
    }

    public function index()
    {
        $episodes = $this->episodeModel
            ->where('is_active', 1)
            ->orderBy('episode_number', 'DESC')
            ->findAll();

        $panelCounts = $this->panelModel
            ->select('episode_id, COUNT(*) as total')
            ->where('is_active', 1)
            ->groupBy('episode_id')
            ->findAll();

        $countsMap = [];
        foreach ($panelCounts as $row) {
            $countsMap[$row['episode_id']] = (int) $row['total'];
        }

        $coverMap = [];
        foreach ($episodes as $episode) {
            $firstPanel = $this->panelModel
                ->where('episode_id', $episode['id'])
                ->where('is_active', 1)
                ->orderBy('panel_number', 'ASC')
                ->first();

            $coverMap[$episode['id']] = $episode['cover_image'] ?: ($firstPanel['image_path'] ?? null);
        }

        $data = [
            'episodes' => $episodes,
            'panelCounts' => $countsMap,
            'coverMap' => $coverMap,
            'store' => $this->storeData,
        ];

        return view('comic/index', $data);
    }

    public function show($slug)
    {
        $episode = $this->episodeModel
            ->where('slug', $slug)
            ->where('is_active', 1)
            ->first();

        if (!$episode) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Episode komik tidak ditemukan.');
        }

        $panels = $this->panelModel
            ->where('episode_id', $episode['id'])
            ->where('is_active', 1)
            ->orderBy('panel_number', 'ASC')
            ->findAll();

        $data = [
            'episode' => $episode,
            'panels' => $panels,
            'store' => $this->storeData,
        ];

        return view('comic/show', $data);
    }
}
