<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class FonnteService
{
    protected $token;

    public function __construct()
    {
        $this->token = config('services.fonnte.token');

        if (!$this->token) {
            throw new Exception("Fonnte API Token belum dikonfigurasi di .env");
        }
    }

    public function sendMessage($phone, $message)
    {
        $response = Http::withHeaders([
            'Authorization' => $this->token,
        ])->asForm()->post('https://api.fonnte.com/send', [
            'target' => $phone,
            'message' => $message
        ]);

        $responseData = $response->json();

        if (!$response->successful()) {
            return [
                'status' => 'failed',
                'message' => $responseData['detail'] ?? 'Unknown error from Fonnte API'
            ];
        }

        return [
            'status' => 'success',
            'response' => $responseData
        ];
    }
}
