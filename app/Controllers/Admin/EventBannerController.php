<?php

namespace App\Controllers\Admin;

use App\Controllers\BaseController;
use App\Models\EventBannerModel;

class EventBannerController extends BaseController
{
    protected $eventBannerModel;

    public function __construct()
    {
        $this->eventBannerModel = new EventBannerModel();
        helper(['image']);
    }

    /**
     * Display list of event banners
     */
    public function index()
    {
        $data = [
            'title' => 'Kelola Event Banner',
            'eventBanners' => $this->eventBannerModel->getAllEventBanners()
        ];

        return view('admin/event_banners/index', $data);
    }

    /**
     * Show form to create new event banner
     */
    public function create()
    {
        $data = [
            'title' => 'Tambah Event Banner'
        ];

        return view('admin/event_banners/create', $data);
    }

    /**
     * Store new event banner
     */
    public function store()
    {
        // Validasi ukuran file terlebih dahulu
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            // Validasi ukuran file (2MB = 2048KB)
            if ($image->getSize() > 2097152) { // 2MB dalam bytes
                return redirect()->back()->withInput()->with('error', 'Ukuran gambar tidak boleh lebih dari 2MB. Ukuran file Anda: ' . round($image->getSize() / 1024 / 1024, 2) . 'MB');
            }
        }

        $validation = $this->validate([
            'title' => 'required|min_length[3]|max_length[255]',
            'image' => 'uploaded[image]|is_image[image]|max_size[image,2048]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp]',
            'link_url' => 'permit_empty|valid_url',
            'start_date' => 'required|valid_date',
            'end_date' => 'required|valid_date',
            'is_active' => 'in_list[0,1]',
            'domain_specific' => 'in_list[0,1]',
            'allowed_domains' => 'permit_empty'
        ]);

        if (!$validation) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Handle file upload
        $image = $this->request->getFile('image');
        $imageName = null;

        if ($image && $image->isValid() && !$image->hasMoved()) {
            $newImageName = upload_and_convert_to_webp($image, FCPATH . 'uploads/event_banners/');
            if ($newImageName) {
                $imageName = $newImageName;
            }
        }

        // Process allowed domains
        $allowedDomains = $this->request->getPost('allowed_domains');
        $domainSpecific = $this->request->getPost('domain_specific') ?? 0;
        
        // Debug: Log what we received for CREATE
        log_message('debug', 'CREATE - Domain Specific: ' . $domainSpecific);
        log_message('debug', 'CREATE - Allowed Domains: ' . print_r($allowedDomains, true));
        
        // Convert domains array to JSON if domain specific is enabled
        $allowedDomainsJson = null;
        if ($domainSpecific == 1 && !empty($allowedDomains) && is_array($allowedDomains)) {
            $allowedDomainsJson = json_encode(array_values($allowedDomains));
            log_message('debug', 'CREATE - Allowed Domains JSON: ' . $allowedDomainsJson);
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'image_url' => $imageName,
            'link_url' => $this->request->getPost('link_url'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'is_active' => $this->request->getPost('is_active') ?? 1,
            'domain_specific' => $domainSpecific,
            'allowed_domains' => $allowedDomainsJson
        ];

        if ($this->eventBannerModel->save($data)) {
            return redirect()->to('/admin/event-banners')->with('success', 'Event banner berhasil ditambahkan.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal menambahkan event banner.');
        }
    }

    /**
     * Show form to edit event banner
     */
    public function edit($id)
    {
        $eventBanner = $this->eventBannerModel->find($id);

        if (!$eventBanner) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Event banner tidak ditemukan.');
        }

        $data = [
            'title' => 'Edit Event Banner',
            'eventBanner' => $eventBanner
        ];

        return view('admin/event_banners/edit', $data);
    }

    /**
     * Update event banner
     */
    public function update($id)
    {
        $eventBanner = $this->eventBannerModel->find($id);

        if (!$eventBanner) {
            throw new \CodeIgniter\Exceptions\PageNotFoundException('Event banner tidak ditemukan.');
        }

        // Validasi ukuran file jika ada file baru yang diupload
        $image = $this->request->getFile('image');
        if ($image && $image->isValid() && !$image->hasMoved()) {
            // Validasi ukuran file (2MB = 2048KB)
            if ($image->getSize() > 2097152) { // 2MB dalam bytes
                return redirect()->back()->withInput()->with('error', 'Ukuran gambar tidak boleh lebih dari 2MB. Ukuran file Anda: ' . round($image->getSize() / 1024 / 1024, 2) . 'MB');
            }
        }

        $validation = $this->validate([
            'title' => 'required|min_length[3]|max_length[255]',
            'image' => 'permit_empty|is_image[image]|max_size[image,2048]|mime_in[image,image/jpg,image/jpeg,image/png,image/webp]',
            'link_url' => 'permit_empty|valid_url',
            'start_date' => 'required|valid_date',
            'end_date' => 'required|valid_date',
            'is_active' => 'in_list[0,1]',
            'domain_specific' => 'in_list[0,1]',
            'allowed_domains' => 'permit_empty'
        ]);

        if (!$validation) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        // Process allowed domains
        $allowedDomains = $this->request->getPost('allowed_domains');
        $domainSpecific = $this->request->getPost('domain_specific') ?? 0;
        
        // Debug: Log what we received for UPDATE
        log_message('debug', 'UPDATE - Domain Specific: ' . $domainSpecific);
        log_message('debug', 'UPDATE - Allowed Domains: ' . print_r($allowedDomains, true));
        
        // Convert domains array to JSON if domain specific is enabled
        $allowedDomainsJson = null;
        if ($domainSpecific == 1 && !empty($allowedDomains) && is_array($allowedDomains)) {
            $allowedDomainsJson = json_encode(array_values($allowedDomains));
            log_message('debug', 'UPDATE - Allowed Domains JSON: ' . $allowedDomainsJson);
        }

        $data = [
            'title' => $this->request->getPost('title'),
            'link_url' => $this->request->getPost('link_url'),
            'start_date' => $this->request->getPost('start_date'),
            'end_date' => $this->request->getPost('end_date'),
            'is_active' => $this->request->getPost('is_active') ?? 1,
            'domain_specific' => $domainSpecific,
            'allowed_domains' => $allowedDomainsJson
        ];

        // Handle file upload if new image is provided
        $image = $this->request->getFile('image');

        if ($image && $image->isValid() && !$image->hasMoved()) {
            $newImageName = upload_and_convert_to_webp($image, FCPATH . 'uploads/event_banners/');
            if ($newImageName) {
                // Delete old image
                if ($eventBanner['image_url'] && file_exists(FCPATH . 'uploads/event_banners/' . $eventBanner['image_url'])) {
                    unlink(FCPATH . 'uploads/event_banners/' . $eventBanner['image_url']);
                }
                $data['image_url'] = $newImageName;
            }
        }

        if ($this->eventBannerModel->update($id, $data)) {
            return redirect()->to('/admin/event-banners')->with('success', 'Event banner berhasil diperbarui.');
        } else {
            return redirect()->back()->withInput()->with('error', 'Gagal memperbarui event banner.');
        }
    }

    /**
     * Delete event banner
     */
    public function delete($id)
    {
        $eventBanner = $this->eventBannerModel->find($id);

        if (!$eventBanner) {
            return redirect()->to('/admin/event-banners')->with('error', 'Event banner tidak ditemukan.');
        }

        // Delete image file
        if ($eventBanner['image_url'] && file_exists(FCPATH . 'uploads/event_banners/' . $eventBanner['image_url'])) {
            unlink(FCPATH . 'uploads/event_banners/' . $eventBanner['image_url']);
        }

        if ($this->eventBannerModel->delete($id)) {
            return redirect()->to('/admin/event-banners')->with('success', 'Event banner berhasil dihapus.');
        } else {
            return redirect()->to('/admin/event-banners')->with('error', 'Gagal menghapus event banner.');
        }
    }

    /**
     * Toggle active status
     */
    public function toggleStatus($id)
    {
        $eventBanner = $this->eventBannerModel->find($id);

        if (!$eventBanner) {
            return $this->response->setJSON(['success' => false, 'message' => 'Event banner tidak ditemukan.']);
        }

        $newStatus = $eventBanner['is_active'] ? 0 : 1;

        if ($this->eventBannerModel->update($id, ['is_active' => $newStatus])) {
            return $this->response->setJSON(['success' => true, 'message' => 'Status berhasil diperbarui.']);
        } else {
            return $this->response->setJSON(['success' => false, 'message' => 'Gagal memperbarui status.']);
        }
    }
}
