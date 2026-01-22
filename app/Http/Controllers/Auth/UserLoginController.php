<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Inertia\Inertia;

class UserLoginController extends Controller
{
    public function show()
    {
        return Inertia::render('Auth/UserLogin');
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

        if (Auth::attempt($request->only('email', 'password'), (bool) $request->boolean('remember'))) {
            RateLimiter::clear($key);
            $request->session()->regenerate();

            // ====== TAMBAHAN WAJIB: cek verifikasi email + status aktif ======
            $user = $request->user();

            if (!$user || !$user->email_verified_at) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Email belum terverifikasi. Silakan registrasi/verifikasi dulu.',
                ])->onlyInput('email');
            }

            if (!$user->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'Akun nonaktif. Hubungi admin.',
                ])->onlyInput('email');
            }
            // ====== END TAMBAHAN ======

            return redirect()->intended(route('user.home'));
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

        return redirect()->route('login'); // ✅ bukan user.login
    }


    private function throttleKey(Request $request): string
    {
        return Str::lower($request->input('email', '')) . '|' . $request->ip();
    }
}
