<?php

namespace App\Filters;

use App\Models\ApiTokenModel;
use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $header = $request->getHeaderLine('Authorization');
        if (!$header || stripos($header, 'Bearer ') !== 0) {
            return $this->unauthorized();
        }

        $token = trim(substr($header, 7));
        if ($token === '') {
            return $this->unauthorized();
        }

        $tokenHash = hash('sha256', $token);
        $now = date('Y-m-d H:i:s');

        $apiTokenModel = new ApiTokenModel();
        $tokenRow = $apiTokenModel
            ->where('token_hash', $tokenHash)
            ->where('revoked_at', null)
            ->groupStart()
                ->where('expires_at', null)
                ->orWhere('expires_at >=', $now)
            ->groupEnd()
            ->first();

        if (!$tokenRow) {
            return $this->unauthorized();
        }

        $apiTokenModel->update($tokenRow['id'], ['last_used_at' => $now]);

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        return null;
    }

    protected function unauthorized(): ResponseInterface
    {
        return service('response')
            ->setStatusCode(ResponseInterface::HTTP_UNAUTHORIZED)
            ->setJSON([
                'status' => 'error',
                'message' => 'Unauthorized',
                'data' => null,
            ]);
    }
}
