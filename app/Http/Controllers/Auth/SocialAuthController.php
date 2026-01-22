<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    private array $allowedProviders = ['google', 'github'];

    public function redirect(string $provider)
    {
        if (!in_array($provider, $this->allowedProviders, true)) {
            abort(404);
        }

        return Socialite::driver($provider)->redirect();
    }

    public function callback(Request $request, string $provider)
    {
        if (!in_array($provider, $this->allowedProviders, true)) {
            abort(404);
        }

        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Throwable $e) {
            return redirect()->route('user.login')->with('flash', [
                'type' => 'error',
                'message' => 'Gagal login sosial. Coba lagi.',
            ]);
        }

        $email = Str::lower(trim((string) $socialUser->getEmail()));
        $name = trim((string) $socialUser->getName());
        if ($name === '') {
            $name = trim((string) $socialUser->getNickname());
        }
        if ($name === '') {
            $name = 'User';
        }

        if (!$email) {
            return redirect()->route('user.login')->with('flash', [
                'type' => 'error',
                'message' => 'Provider tidak memberikan email. Tambahkan email pada akun provider kamu lalu coba lagi.',
            ]);
        }

        if (!Str::endsWith($email, '@gmail.com')) {
            return redirect()->route('user.login')->with('flash', [
                'type' => 'error',
                'message' => 'Email harus @gmail.com.',
            ]);
        }

        $user = User::where('email', $email)->first();
        if (!$user) {
            $user = new User();
            $user->name = $name;
            $user->email = $email;
            $user->password = Hash::make(Str::random(32)); // tidak dipakai, tapi wajib ada
            $user->email_verified_at = now();
            $user->is_active = true;
            $user->save();

            Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
            $user->assignRole('user');
        } else {
            if (!$user->email_verified_at) {
                $user->email_verified_at = now();
            }
            $user->is_active = true;
            $user->save();
        }

        Auth::login($user, true);

        // Selalu masuk portal user; kalau mau admin, tetap harus punya role admin dan login lewat /admin/login
        return redirect()->route('user.home');
    }
}
