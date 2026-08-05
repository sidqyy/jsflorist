<?php

namespace App\Libraries;

use CodeIgniter\HTTP\CURLRequest;
use Config\Services;

class Fonnte
{
    protected $token;
    protected $apiUrl = 'https://api.fonnte.com/send';

    public function __construct()
    {
        // Load the token from the .env file
        $this->token = getenv('FONNTE_TOKEN');
    }

    /**
     * Send a WhatsApp message via Fonnte
     *
     * @param string $target Phone number(s) (comma separated for multiple)
     * @param string $message The text message to send
     * @return mixed
     */
    public function sendMessage($target, $message)
    {
        $client = Services::curlrequest();

        try {
            $response = $client->post($this->apiUrl, [
                'headers' => [
                    'Authorization' => $this->token
                ],
                'form_params' => [
                    'target' => $target,
                    'message' => $message,
                    'delay' => '2',
                    'countryCode' => '62', // Default to Indonesia
                ]
            ]);

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return [
                'status' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
