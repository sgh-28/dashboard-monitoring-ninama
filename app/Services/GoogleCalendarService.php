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
        $this->client->setApplicationName(env('GOOGLE_APP_NAME', 'Ninama Dashboard'));
        $this->client->setClientId(env('GOOGLE_CLIENT_ID'));
        $this->client->setClientSecret(env('GOOGLE_CLIENT_SECRET'));
        $this->client->setRedirectUri(env('GOOGLE_REDIRECT_URI', 'http://ninama-dashboard.test/auth/google/callback'));
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

    public function createEvent($title, $date)
    {
        try {
            $event = new Calendar\Event([
                'summary' => $title,
                'start' => ['date' => $date],
                'end'   => ['date' => $date],
            ]);

            $createdEvent = $this->service->events->insert('primary', $event);

            return [
                'status' => 'success',
                'event_link' => $createdEvent->htmlLink,
            ];
        } catch (Exception $e) {
            return [
                'status' => 'error',
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
}