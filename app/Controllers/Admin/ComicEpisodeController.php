<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ComicEpisodeModel;
use App\Models\ComicPanelModel;

class ComicEpisodeController extends BaseController
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
            ->orderBy('episode_number', 'DESC')
            ->paginate(10, 'comic_episodes');

        $episodeIds = array_column($episodes, 'id');
        $panelCounts = [];

        if (!empty($episodeIds)) {
            $rows = $this->panelModel
                ->select('episode_id, COUNT(*) as total')
                ->whereIn('episode_id', $episodeIds)
                ->groupBy('episode_id')
                ->findAll();

            foreach ($rows as $row) {
                $panelCounts[$row['episode_id']] = (int) $row['total'];
            }
        }

        $data = [
            'episodes' => $episodes,
            'panelCounts' => $panelCounts,
            'pager' => $this->episodeModel->pager,
            'title' => 'Manajemen Komik',
        ];

        return view('admin/comics/index', $data);
    }

    public function create()
    {
        $data = [
            'title' => 'Tambah Episode Komik',
        ];

        return view('admin/comics/create', $data);
    }

    public function store()
    {
        helper(['text', 'image']);

        $rules = [
            'title' => 'required|min_length[3]',
            'episode_number' => 'required|integer',
            'cover_image' => 'permit_empty|is_image[cover_image]|max_size[cover_image,2048]|mime_in[cover_image,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $title = $this->request->getPost('title');
        $slug = url_title($title, '-', true);
        $originalSlug = $slug;
        $i = 0;

        while ($this->episodeModel->where('slug', $slug)->first()) {
            $i++;
            $slug = $originalSlug . '-' . $i;
        }

        $coverFile = $this->request->getFile('cover_image');
        $coverName = null;

        if ($coverFile && $coverFile->isValid() && !$coverFile->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/comics/episodes/';
            $newCoverName = upload_and_convert_to_webp($coverFile, $uploadPath);
            if ($newCoverName) {
                $coverName = $newCoverName;
            }
        }

        $data = [
            'episode_number' => (int) $this->request->getPost('episode_number'),
            'title' => $title,
            'slug' => $slug,
            'description' => $this->request->getPost('description'),
            'cover_image' => $coverName,
            'is_active' => $this->request->getPost('is_active') ?? 1,
        ];

        if ($this->episodeModel->save($data)) {
            return redirect()->to('/admin/comics')->with('success', 'Episode komik berhasil ditambahkan.');
        }

        return redirect()->back()->withInput()->with('errors', ['Gagal menyimpan episode komik.']);
    }

    public function edit($id)
    {
        $episode = $this->episodeModel->find($id);

        if (!$episode) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Episode komik tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Episode Komik',
            'episode' => $episode,
        ];

        return view('admin/comics/edit', $data);
    }

    public function update($id)
    {
        helper(['text', 'image']);

        $episode = $this->episodeModel->find($id);
        if (!$episode) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Episode komik tidak ditemukan.');
        }

        $rules = [
            'title' => 'required|min_length[3]',
            'episode_number' => 'required|integer',
            'cover_image' => 'permit_empty|is_image[cover_image]|max_size[cover_image,2048]|mime_in[cover_image,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $title = $this->request->getPost('title');
        $slug = url_title($title, '-', true);
        $originalSlug = $slug;
        $i = 0;

        while ($existing = $this->episodeModel->where('slug', $slug)->first()) {
            if ((int) $existing['id'] === (int) $id) {
                break;
            }
            $i++;
            $slug = $originalSlug . '-' . $i;
        }

        $coverFile = $this->request->getFile('cover_image');
        $coverName = $episode['cover_image'] ?? null;

        if ($coverFile && $coverFile->isValid() && !$coverFile->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/comics/episodes/';
            $newCoverName = upload_and_convert_to_webp($coverFile, $uploadPath);
            if ($newCoverName) {
                if (!empty($coverName) && file_exists($uploadPath . $coverName)) {
                    unlink($uploadPath . $coverName);
                }
                $coverName = $newCoverName;
            }
        }

        $data = [
            'episode_number' => (int) $this->request->getPost('episode_number'),
            'title' => $title,
            'slug' => $slug,
            'description' => $this->request->getPost('description'),
            'cover_image' => $coverName,
            'is_active' => $this->request->getPost('is_active') ?? 1,
        ];

        if ($this->episodeModel->update($id, $data)) {
            return redirect()->to('/admin/comics')->with('success', 'Episode komik berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('errors', ['Gagal memperbarui episode komik.']);
    }

    public function delete($id)
    {
        $episode = $this->episodeModel->find($id);
        if (!$episode) {
            return redirect()->to('/admin/comics')->with('error', 'Episode komik tidak ditemukan.');
        }

        $uploadEpisodePath = FCPATH . 'uploads/comics/episodes/';
        if (!empty($episode['cover_image']) && file_exists($uploadEpisodePath . $episode['cover_image'])) {
            unlink($uploadEpisodePath . $episode['cover_image']);
        }

        $panels = $this->panelModel->where('episode_id', $id)->findAll();
        $uploadPanelPath = FCPATH . 'uploads/comics/panels/';
        foreach ($panels as $panel) {
            if (!empty($panel['image_path']) && file_exists($uploadPanelPath . $panel['image_path'])) {
                unlink($uploadPanelPath . $panel['image_path']);
            }
        }

        $this->panelModel->where('episode_id', $id)->delete();

        if ($this->episodeModel->delete($id)) {
            return redirect()->to('/admin/comics')->with('success', 'Episode komik berhasil dihapus.');
        }

        return redirect()->to('/admin/comics')->with('error', 'Gagal menghapus episode komik.');
    }

    public function toggleStatus($id)
    {
        $episode = $this->episodeModel->find($id);

        if (!$episode) {
            return $this->response->setJSON(['success' => false, 'message' => 'Episode komik tidak ditemukan.']);
        }

        $newStatus = $episode['is_active'] ? 0 : 1;

        if ($this->episodeModel->update($id, ['is_active' => $newStatus])) {
            return $this->response->setJSON(['success' => true, 'message' => 'Status berhasil diperbarui.']);
        }

        return $this->response->setJSON(['success' => false, 'message' => 'Gagal memperbarui status.']);
    }
}
