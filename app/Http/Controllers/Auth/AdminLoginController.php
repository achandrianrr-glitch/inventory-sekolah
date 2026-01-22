<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AdminLoginController extends Controller
{
    public function show()
    {
        return Inertia::render('Auth/AdminLogin');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $key = $this->throttleKey($request);

        if (RateLimiter::tooManyAttempts($key, 10)) {
            $seconds = RateLimiter::availableIn($key);

            return back()->withErrors([
                'email' => "Terlalu banyak percobaan. Coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, (bool) $request->boolean('remember'))) {
            RateLimiter::clear($key);
            $request->session()->regenerate();

            // 1) CEK EMAIL VERIFIED DULU
            $user = $request->user();

            if (!$user || !$user->email_verified_at) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Email admin belum terverifikasi.',
                ])->onlyInput('email');
            }

            // 2) BARU CEK ROLE ADMIN
            if (!$user->hasAnyRole(['super_admin', 'petugas'])) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun ini bukan admin.',
                ])->onlyInput('email');
            }

            return redirect()->route('admin.dashboard');
        }

        RateLimiter::hit($key, 60);

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        \Illuminate\Support\Facades\Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login');
    }


    private function throttleKey(Request $request): string
    {
        return 'admin|' . Str::lower($request->input('email', '')) . '|' . $request->ip();
    }
}
