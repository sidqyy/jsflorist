<?php

namespace App\Controllers;

use App\Models\SettingModel;
use CodeIgniter\Controller;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use Psr\Log\LoggerInterface;

abstract class BaseController extends Controller
{
    /**
     * Instance of the main Request object.
     *
     * @var CLIRequest|IncomingRequest
     */
    protected $request;

    /**
     * An array of helpers to be loaded automatically upon
     * class instantiation. These helpers will be available
     * to all other controllers that extend BaseController.
     *
     * @var list<string>
     */
    protected $helpers = [];

    /**
     * Be sure to declare properties for any property fetch you initialized.
     * The creation of dynamic property is deprecated in PHP 8.2.
     */
    protected $session;
    protected array $storeData; // Deklarasi properti untuk menghindari deprecated warning

    /**
     * @return void
     */
    public function initController(RequestInterface $request, ResponseInterface $response, LoggerInterface $logger)
    {
        // Do Not Edit This Line
        parent::initController($request, $response, $logger);

        // Preload any models, libraries, etc, here.
        $this->session = \Config\Services::session();
        $currentDomain = $_SERVER['HTTP_HOST'];

        $this->storeData = [];

        if (strpos($currentDomain, 'jsflorist.com') !== false) {
            $this->storeData = [
                'name' => 'JS Florist',
                'logo_url' => 'assets/img/logo_js.svg',
                'address' => 'Jl. Dahlia No.23, Komet, Kec. Banjarbaru Selatan, Kota Banjar Baru, Kalimantan Selatan 70714',
                'email' => 'adminjs@jsflorist.com',
                'phone' => '+62823-5741-8002',
                'instagram' => 'https://www.instagram.com/jsflorist.banjarbaru/',
                'facebook' => 'https://facebook.com/jsflorist',
                'youtube' => 'https://youtube.com/jsflorist',
                'linkedin' => 'https://linkedin.com/jsflorist',
                'favicon_url' => 'assets/img/logo_js.svg', // Path diperbaiki
                'gmaps_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3982.629879418118!2d114.83060487351868!3d-3.439882841769493!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2de681005cf941bd%3A0x75ae39760ce6a15e!2sJS%20Florist%20Banjarbaru!5e0!3m2!1sid!2sid!4v1753327409248!5m2!1sid!2sid',
                // Domain-specific Google Place ID for reviews (set in .env)
                'google_place_id' => env('google.placeId_js') ?? env('google.placeId'),
            ];
        } elseif (strpos($currentDomain, 'poppyflorist.com') !== false) {
            // Hide address/contact/socials for Poppy Florist domain.
            $this->storeData = [
                'name' => 'JS Florist',
                'logo_url' => 'assets/img/logo_js.svg',
                'email' => 'adminjs@jsflorist.com',
                'favicon_url' => 'assets/img/logo_js.svg',
                'address' => '',
                'phone' => '',
                'instagram' => '',
                'facebook' => '',
                'youtube' => '',
                'linkedin' => '',
                'gmaps_url' => '',
                'google_place_id' => env('google.placeId_default') ?? env('google.placeId_js') ?? env('google.placeId'),
            ];
        } else {
            // Default fallback: treat as JS Florist
            $this->storeData = [
                'name' => 'JS Florist',
                'logo_url' => 'assets/img/logo_js.svg',
                'address' => 'Jl. Dahlia No.23, Komet, Kec. Banjarbaru Selatan, Kota Banjar Baru, Kalimantan Selatan 70714',
                'email' => 'adminjs@jsflorist.com',
                'phone' => '+62823-5741-8002',
                'instagram' => 'https://www.instagram.com/jsflorist.banjarbaru/',
                'facebook' => 'https://facebook.com/jsflorist',
                'youtube' => 'https://youtube.com/jsflorist',
                'linkedin' => 'https://linkedin.com/jsflorist',
                'favicon_url' => 'assets/img/logo_js.svg',
                'gmaps_url' => 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3982.629879418118!2d114.83060487351868!3d-3.439882841769493!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2de681005cf941bd%3A0x75ae39760ce6a15e!2sJS%20Florist%20Banjarbaru!5e0!3m2!1sid!2sid!4v1753327409248!5m2!1sid!2sid',
                // Fallback Place ID (use js as default or provide google.placeId_default)
                'google_place_id' => env('google.placeId_default') ?? env('google.placeId_js') ?? env('google.placeId'),
            ];
        }

        service('renderer')->setVar('store', $this->storeData);

        if (!empty($this->storeData['favicon_url'])) {
            $faviconFileName = basename($this->storeData['favicon_url']);
            $faviconExtension = pathinfo($faviconFileName, PATHINFO_EXTENSION);
            $faviconType = 'image/x-icon';
            if ($faviconExtension === 'png') {
                $faviconType = 'image/png';
            } elseif ($faviconExtension === 'svg') {
                $faviconType = 'image/svg+xml';
            }
            service('renderer')->setVar('currentFaviconType', $faviconType);
        }

        $settingModel = new SettingModel();

        if (!$this->session->has('visitor_counted')) {
            $settingModel->increment('visitor_count');
            $this->session->set('visitor_counted', true);
        }

        $currentCount = (int) $settingModel->getSetting('visitor_count');
        $displayCount = $currentCount + 200;

        service('renderer')->setVar('displayVisitorCount', $displayCount);
    }
}