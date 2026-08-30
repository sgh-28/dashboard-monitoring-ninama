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

        // Admin & Direktur → Dashboard Utama (/)
        if ($roleName === 'admin') {
            return redirect()->intended('/dashboard');
        }

        if ($roleName === 'direktur') {
            return redirect()->intended('/');
        }

        // Pegawai → Dashboard Pegawai
        if (in_array($roleName, ['pegawai', 'project_manager'], true)) {
            return redirect()->intended('/my-tasks');
        }

        // Customer → Portal Customer
        if ($roleName === 'customer') {
            return redirect()->intended('/customer/dashboard');
        }

        // Fallback
        return redirect('/login');
    }
}
