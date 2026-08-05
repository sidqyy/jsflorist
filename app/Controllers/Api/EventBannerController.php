<?php

namespace App\Controllers\Api;

use App\Controllers\BaseController;
use App\Models\EventBannerModel;
use CodeIgniter\HTTP\ResponseInterface;

class EventBannerController extends BaseController
{
    protected EventBannerModel $eventBannerModel;

    public function __construct()
    {
        helper(['url']);
        $this->eventBannerModel = new EventBannerModel();
    }

    public function index(): ResponseInterface
    {
        $domain = trim((string) ($this->request->getGet('domain') ?? ''));
        if ($domain === '') {
            $domain = $this->request->getHeaderLine('X-Frontend-Domain');
        }
        if ($domain === '') {
            $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        }

        $banners = $this->eventBannerModel->getActiveEventBanners($domain);

        $payload = array_map(function (array $banner) {
            return [
                'id' => (int) $banner['id'],
                'title' => $banner['title'],
                'image_url' => !empty($banner['image_url'])
                    ? base_url('uploads/event_banners/' . $banner['image_url'])
                    : null,
                'link_url' => $banner['link_url'] ?: null,
                
                // Tambahan: Label untuk tombol di React Poppy Florist
                'link_label' => !empty($banner['link_url']) ? 'Klik untuk membuka' : null,
                
                'start_date' => $banner['start_date'] ?? null,
                'end_date' => $banner['end_date'] ?? null,
                'is_active' => (int) ($banner['is_active'] ?? 0),
                'domain_specific' => (int) ($banner['domain_specific'] ?? 0),
                'allowed_domains' => !empty($banner['allowed_domains'])
                    ? json_decode($banner['allowed_domains'], true)
                    : [],
            ];
        }, $banners);

        return $this->response->setJSON([
            'status' => 'success',
            'data' => [
                'domain' => $domain,
                'count' => count($payload),
                'banners' => $payload,
            ],
        ]);
    }
}