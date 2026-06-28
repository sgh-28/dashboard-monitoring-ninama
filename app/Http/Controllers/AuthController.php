<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        if (Auth::check()) {
            return $this->redirectBasedOnRole(Auth::user());
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            // ✅ Load role dan redirect
            $user = Auth::user()->load('role');
            
            return $this->redirectBasedOnRole($user);
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }

    protected function redirectBasedOnRole($user)
    {
        $roleName = $user->role?->name ?? '';

        // Super Admin & Direktur → Dashboard Utama (/)
        if (in_array($roleName, ['super_admin', 'direktur'])) {
            return redirect()->intended('/');
        }

        // Pegawai → Dashboard Pegawai
        if ($roleName === 'pegawai') {
            return redirect()->intended('/employee/dashboard');
        }

        // Customer → Portal Customer
        if ($roleName === 'customer') {
            return redirect()->intended('/customer/dashboard');
        }

        // Fallback
        return redirect('/login');
    }
}