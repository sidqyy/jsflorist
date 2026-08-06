<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiCorsFilter implements FilterInterface
{
    protected array $allowedOrigins = [
        'https://jsflorist.com',
        'https://www.jsflorist.com',
        'https://poppyflorist.jsflorist.com',
        'https://www.poppyflorist.jsflorist.com',
        'https://poppyflorist.com',
        'https://www.poppyflorist.com',
        'https://game.jsflorist.com',
        'https://www.game.jsflorist.com',
        'http://localhost',
        'http://localhost:8080',
        'http://localhost:8000',
        'http://127.0.0.1',
        'http://poppyflorist.test',
    ];

    public function before(RequestInterface $request, $arguments = null)
    {
        $origin = $request->getHeaderLine('Origin');
        
        // PERBAIKAN FATAL: Memaksa method dibaca sebagai huruf kapital besar (OPTIONS)
        if (strtoupper($request->getMethod()) === 'OPTIONS') {
            // Berikan status 200 OK agar disetujui instan oleh browser Chrome/peta Leaflet
            $response = service('response')->setStatusCode(200);
            return $this->applyHeaders($response, $origin);
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        $origin = $request->getHeaderLine('Origin');
        return $this->applyHeaders($response, $origin);
    }

    protected function applyHeaders(ResponseInterface $response, string $origin): ResponseInterface
    {
        if ($origin && in_array($origin, $this->allowedOrigins, true)) {
            $response->setHeader('Access-Control-Allow-Origin', $origin);
        }

        return $response
            ->setHeader('Vary', 'Origin')
            ->setHeader('Access-Control-Allow-Credentials', 'true')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE')
            ->setHeader('Access-Control-Allow-Headers', 'Authorization, Content-Type, X-Requested-With, Accept, X-API-KEY, X-CSRF-TOKEN, X-Frontend-Domain');
    }
}