<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\GoogleCalendarService;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle()
    {
        $service = new GoogleCalendarService();
        return redirect()->away($service->getAuthUrl());
    }

    public function handleGoogleCallback(Request $request)
    {
        $code = $request->get('code');
        if ($code) {
            $service = new GoogleCalendarService();
            $service->saveToken($code);
            return redirect()->route('dashboard')->with('success', 'Google Calendar terhubung berhasil!');
        }
        return redirect()->route('dashboard')->with('error', 'Koneksi Google Calendar gagal.');
    }
}