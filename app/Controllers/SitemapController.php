<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductModel;
use App\Models\ArtikelModel;

class SitemapController extends BaseController
{
    public function index()
    {
        $productModel = new ProductModel();
        $artikelModel = new ArtikelModel();

        $products = $productModel->select('product_id, tanggal_diupdate')->where('is_active', 1)->findAll();
        $articles = $artikelModel->select('slug, tanggal_dibuat')->findAll();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>';
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">';

        // Home page
        $xml .= '<url>';
        $xml .= '<loc>' . base_url() . '</loc>';
        $xml .= '<lastmod>' . date('Y-m-d') . '</lastmod>';
        $xml .= '<changefreq>daily</changefreq>';
        $xml .= '<priority>1.0</priority>';
        $xml .= '</url>';

        // Shop page
        $xml .= '<url>';
        $xml .= '<loc>' . base_url('shop') . '</loc>';
        $xml .= '<lastmod>' . date('Y-m-d') . '</lastmod>';
        $xml .= '<changefreq>weekly</changefreq>';
        $xml .= '<priority>0.8</priority>';
        $xml .= '</url>';

        foreach ($products as $product) {
            $xml .= '<url>';
            $xml .= '<loc>' . base_url('shop/product/' . esc($product['product_id'])) . '</loc>';
            $xml .= '<lastmod>' . date('Y-m-d', strtotime($product['tanggal_diupdate'])) . '</lastmod>';
            $xml .= '<changefreq>monthly</changefreq>';
            $xml .= '<priority>0.7</priority>';
            $xml .= '</url>';
        }

        // Return Policy page (GMC requirement)
        $xml .= '<url>';
        $xml .= '<loc>' . base_url('return-policy') . '</loc>';
        $xml .= '<lastmod>' . date('Y-m-d') . '</lastmod>';
        $xml .= '<changefreq>yearly</changefreq>';
        $xml .= '<priority>0.6</priority>';
        $xml .= '</url>';

        // Articles page
        $xml .= '<url>';
        $xml .= '<loc>' . base_url('artikel') . '</loc>';
        $xml .= '<lastmod>' . date('Y-m-d') . '</lastmod>';
        $xml .= '<changefreq>weekly</changefreq>';
        $xml .= '<priority>0.8</priority>';
        $xml .= '</url>';



        foreach ($articles as $article) {
            $xml .= '<url>';
            $xml .= '<loc>' . base_url('artikel/' . esc($article['slug'])) . '</loc>';
            $xml .= '<lastmod>' . date('Y-m-d', strtotime($article['tanggal_dibuat'])) . '</lastmod>';
            $xml .= '<changefreq>weekly</changefreq>';
            $xml .= '<priority>0.8</priority>';
            $xml .= '</url>';
        }

        $xml .= '</urlset>';

        $this->response->setStatusCode(200);
        $this->response->setContentType('application/xml');
        $this->response->setBody($xml);

        return $this->response;
    }
}
