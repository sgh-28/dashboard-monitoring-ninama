<?php

namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use Exception;

class GoogleCalendarService
{
    protected $client;
    protected $service;
    protected $tokenPath;

    public function __construct()
    {
        // ✅ PERBAIKAN: Simpan token di storage/app, bukan root directory
        $this->tokenPath = storage_path('app/google-token.json');
        
        $this->client = new Client();
        $this->client->setApplicationName(config('services.google.app_name'));
        $this->client->setClientId(config('services.google.client_id'));
        $this->client->setClientSecret(config('services.google.client_secret'));
        $this->client->setRedirectUri(config('services.google.redirect'));
        $this->client->setAccessType('offline');
        $this->client->setPrompt('consent');
        $this->client->setIncludeGrantedScopes(true);
        $this->client->setScopes([Calendar::CALENDAR]);

        // Load token jika ada
        if (file_exists($this->tokenPath)) {
            $savedToken = json_decode(file_get_contents($this->tokenPath), true);
            if (is_array($savedToken) && isset($savedToken['access_token'])) {
                $this->client->setAccessToken($savedToken);
            } else {
                @unlink($this->tokenPath);
            }
        }

        // Refresh token jika expired
        if ($this->client->isAccessTokenExpired()) {
            $refreshToken = $this->client->getRefreshToken();
            if ($refreshToken) {
                $newToken = $this->client->fetchAccessTokenWithRefreshToken($refreshToken);
                if (isset($newToken['access_token'])) {
                    $newToken['refresh_token'] = $refreshToken;
                    $this->saveTokenToFile($newToken);
                    $this->client->setAccessToken($newToken);
                }
            }
        }

        $this->service = new Calendar($this->client);
    }

    protected function saveTokenToFile($token)
    {
        $dir = dirname($this->tokenPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        file_put_contents($this->tokenPath, json_encode($token));
    }

    public static function isConnected(): bool
    {
        $tokenPath = storage_path('app/google-token.json');

        if (!file_exists($tokenPath)) {
            return false;
        }

        $savedToken = json_decode(file_get_contents($tokenPath), true);

        return is_array($savedToken) && isset($savedToken['access_token']);
    }

    public function createEvent($title, $date, $attendeeEmail = null, $description = null)
    {
        try {
            $eventData = [
                'summary'     => $title,
                'description' => $description ?? '',
                'start'       => ['date' => $date],
                'end'         => ['date' => $date],
            ];

            // Tambahkan pegawai sebagai attendee agar undangan masuk ke Google Calendar mereka
            if ($attendeeEmail) {
                $eventData['attendees'] = [
                    ['email' => $attendeeEmail]
                ];
            }

            $event = new Calendar\Event($eventData);

            // sendUpdates='all' agar Google otomatis mengirimkan email undangan ke pegawai
            $createdEvent = $this->service->events->insert('primary', $event, [
                'sendUpdates' => 'all',
            ]);

            return [
                'status'     => 'success',
                'event_id'   => $createdEvent->id,
                'event_link' => $createdEvent->htmlLink,
            ];
        } catch (Exception $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getEvents()
    {
        try {
            $events = $this->service->events->listEvents('primary');
            return $events->getItems();
        } catch (Exception $e) {
            return [
                'status' => 'error',
                'message' => $e->getMessage(),
            ];
        }
    }

    public function getAuthUrl()
    {    
         return $this->client->createAuthUrl();
    }

    public function saveToken($authCode)
    {
        $accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);

        if (!isset($accessToken['access_token'])) {
            throw new Exception("Invalid token received");
        }

        if ($this->client->getRefreshToken()) {
            $accessToken['refresh_token'] = $this->client->getRefreshToken();
        }

        $this->saveTokenToFile($accessToken);

        return $accessToken;
    }

    public function disconnect(): bool
    {
        try {
            if (file_exists($this->tokenPath)) {
                $savedToken = json_decode(file_get_contents($this->tokenPath), true);
                $tokenToRevoke = $savedToken['refresh_token'] ?? $savedToken['access_token'] ?? null;

                if ($tokenToRevoke) {
                    $this->client->revokeToken($tokenToRevoke);
                }

                return @unlink($this->tokenPath);
            }

            return true;
        } catch (Exception $e) {
            if (file_exists($this->tokenPath)) {
                @unlink($this->tokenPath);
            }

            return false;
        }
    }
}
