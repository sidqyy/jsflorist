<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\ComicEpisodeModel;
use App\Models\ComicPanelModel;

class ComicPanelController extends BaseController
{
    protected $episodeModel;
    protected $panelModel;

    public function __construct()
    {
        $this->episodeModel = new ComicEpisodeModel();
        $this->panelModel = new ComicPanelModel();
        helper(['image']);
    }

    public function index($episodeId)
    {
        $episode = $this->episodeModel->find($episodeId);
        if (!$episode) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Episode komik tidak ditemukan.');
        }

        $panels = $this->panelModel
            ->where('episode_id', $episodeId)
            ->orderBy('panel_number', 'ASC')
            ->findAll();

        $data = [
            'episode' => $episode,
            'panels' => $panels,
            'title' => 'Panel Komik: ' . $episode['title'],
        ];

        return view('admin/comic_panels/index', $data);
    }

    public function create($episodeId)
    {
        $episode = $this->episodeModel->find($episodeId);
        if (!$episode) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Episode komik tidak ditemukan.');
        }

        $data = [
            'episode' => $episode,
            'title' => 'Tambah Panel Komik',
        ];

        return view('admin/comic_panels/create', $data);
    }

    public function store($episodeId)
    {
        $episode = $this->episodeModel->find($episodeId);
        if (!$episode) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Episode komik tidak ditemukan.');
        }

        $rules = [
            'panel_number' => 'required|integer',
            'panel_images' => 'uploaded[panel_images]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $panelFiles = $this->request->getFileMultiple('panel_images');
        if (empty($panelFiles)) {
            return redirect()->back()->withInput()->with('errors', ['Gambar panel wajib diunggah.']);
        }

        $uploadPath = FCPATH . 'uploads/comics/panels/';
        if (!is_dir($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $startNumber = (int) $this->request->getPost('panel_number');
        $currentNumber = $startNumber;
        $isActive = $this->request->getPost('is_active') ?? 1;
        $caption = $this->request->getPost('caption');

        foreach ($panelFiles as $panelFile) {
            if (!$panelFile || !$panelFile->isValid() || $panelFile->hasMoved()) {
                continue;
            }

            $mime = $panelFile->getMimeType();
            $allowed = ['image/jpg', 'image/jpeg', 'image/png', 'image/webp'];
            if (!in_array($mime, $allowed, true) || $panelFile->getSize() > 2097152) {
                return redirect()->back()->withInput()->with('errors', ['Format gambar harus JPG/JPEG/PNG/WEBP dan maksimal 2MB.']);
            }

            $newPanelName = upload_and_convert_to_webp($panelFile, $uploadPath);
            if ($newPanelName) {
                $panelName = $newPanelName;
            } else {
                continue;
            }

            $data = [
                'episode_id' => (int) $episodeId,
                'panel_number' => $currentNumber,
                'image_path' => $panelName,
                'caption' => $caption,
                'is_active' => $isActive,
            ];

            $this->panelModel->save($data);
            $currentNumber++;
        }

        return redirect()->to('/admin/comics/' . $episodeId . '/panels')->with('success', 'Panel komik berhasil ditambahkan.');
    }

    public function edit($episodeId, $panelId)
    {
        $episode = $this->episodeModel->find($episodeId);
        if (!$episode) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Episode komik tidak ditemukan.');
        }

        $panel = $this->panelModel->find($panelId);
        if (!$panel || (int) $panel['episode_id'] !== (int) $episodeId) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Panel komik tidak ditemukan.');
        }

        $data = [
            'episode' => $episode,
            'panel' => $panel,
            'title' => 'Edit Panel Komik',
        ];

        return view('admin/comic_panels/edit', $data);
    }

    public function update($episodeId, $panelId)
    {
        $episode = $this->episodeModel->find($episodeId);
        if (!$episode) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Episode komik tidak ditemukan.');
        }

        $panel = $this->panelModel->find($panelId);
        if (!$panel || (int) $panel['episode_id'] !== (int) $episodeId) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Panel komik tidak ditemukan.');
        }

        $rules = [
            'panel_number' => 'required|integer',
            'panel_image' => 'permit_empty|is_image[panel_image]|max_size[panel_image,2048]|mime_in[panel_image,image/jpg,image/jpeg,image/png,image/webp]',
        ];

        if (!$this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $panelName = $panel['image_path'] ?? null;
        $panelFile = $this->request->getFile('panel_image');

        if ($panelFile && $panelFile->isValid() && !$panelFile->hasMoved()) {
            $uploadPath = FCPATH . 'uploads/comics/panels/';
            $newPanelName = upload_and_convert_to_webp($panelFile, $uploadPath);
            if ($newPanelName) {
                if (!empty($panelName) && file_exists($uploadPath . $panelName)) {
                    unlink($uploadPath . $panelName);
                }
                $panelName = $newPanelName;
            }
        }

        $data = [
            'panel_number' => (int) $this->request->getPost('panel_number'),
            'image_path' => $panelName,
            'caption' => $this->request->getPost('caption'),
            'is_active' => $this->request->getPost('is_active') ?? 1,
        ];

        if ($this->panelModel->update($panelId, $data)) {
            return redirect()->to('/admin/comics/' . $episodeId . '/panels')->with('success', 'Panel komik berhasil diperbarui.');
        }

        return redirect()->back()->withInput()->with('errors', ['Gagal memperbarui panel komik.']);
    }

    public function delete($episodeId, $panelId)
    {
        $panel = $this->panelModel->find($panelId);
        if (!$panel || (int) $panel['episode_id'] !== (int) $episodeId) {
            return redirect()->to('/admin/comics/' . $episodeId . '/panels')->with('error', 'Panel komik tidak ditemukan.');
        }

        $uploadPath = FCPATH . 'uploads/comics/panels/';
        if (!empty($panel['image_path']) && file_exists($uploadPath . $panel['image_path'])) {
            unlink($uploadPath . $panel['image_path']);
        }

        if ($this->panelModel->delete($panelId)) {
            return redirect()->to('/admin/comics/' . $episodeId . '/panels')->with('success', 'Panel komik berhasil dihapus.');
        }

        return redirect()->to('/admin/comics/' . $episodeId . '/panels')->with('error', 'Gagal menghapus panel komik.');
    }
}
