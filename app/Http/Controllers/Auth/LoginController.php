<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Services\GoogleCalendarService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Tampilkan form login
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Handle login attempt
     */
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();

            // ✅ REDIRECT BERDASARKAN ROLE
            $user = Auth::user();
            $role = $user->role->name ?? 'user';

            return match ($role) {
                'direktur'    => redirect()->route('direktur.dashboard'),
                'admin' => GoogleCalendarService::isConnected()
                    ? redirect()->route('main.dashboard')
                    : redirect()->route('auth.google'),
                'pegawai'     => redirect()->route('employee.tasks.index'),
                'customer'    => redirect()->route('customer.dashboard'),
                'marketing'   => redirect()->route('marketing.index'),
                default       => redirect()->route('main.dashboard'),
            };
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    /**
     * Handle logout
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        if (($user->role->name ?? null) === 'admin') {
            app(GoogleCalendarService::class)->disconnect();
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
