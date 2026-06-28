<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Exception;

class WhatsAppService
{
    public function sendMessage($phone, $message)
    {
        $token = config('services.fonnte.token');

        $response = Http::withHeaders([
            'Authorization' => $token,
        ])->post('https://api.fonnte.com/send', [
            'target' => $phone,
            'message' => $message,
        ]);

        if (!$response->successful()) {
            throw new Exception("Error sending WhatsApp: " . $response->body());
        }

        return $response->json();
    }
}
