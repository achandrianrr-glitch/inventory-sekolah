<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\OtpCodeMail;
use App\Models\Role;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Inertia;

class RegisterOtpController extends Controller
{
    private const OTP_LENGTH = 6;
    private const OTP_EXPIRE_MINUTES = 60;

    private const OTP_MAX_ATTEMPTS = 5;

    private const RESEND_COOLDOWN_SECONDS = 60;  // minimal 60 detik antar kirim
    private const SEND_LIMIT_PER_HOUR = 6;        // limit kirim OTP / jam (per email+ip)

    public function showEmailForm()
    {
        return Inertia::render('Auth/RegisterEmail');
    }

    public function requestOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = Str::lower(trim((string) $request->input('email')));

        if (!$this->isGmail($email)) {
            return back()->withErrors(['email' => 'Email harus @gmail.com.'])->onlyInput('email');
        }

        // Jika sudah terdaftar & terverifikasi -> arahkan login
        $existing = User::where('email', $email)->first();
        if ($existing && $existing->email_verified_at) {
            return $this->redirectToLogin()->with('flash', [
                'type' => 'info',
                'message' => 'Email sudah terdaftar. Silakan login.',
            ]);
        }

        $rateKey = $this->sendRateKey($email, (string) $request->ip());
        if (RateLimiter::tooManyAttempts($rateKey, self::SEND_LIMIT_PER_HOUR)) {
            $seconds = RateLimiter::availableIn($rateKey);
            return back()->withErrors([
                'email' => "Terlalu sering meminta OTP. Coba lagi dalam {$seconds} detik.",
            ])->onlyInput('email');
        }

        $now = Carbon::now();

        // Hapus OTP lama yang belum verified (biar 1 email 1 OTP aktif)
        DB::table('email_otps')
            ->where('email', $email)
            ->whereNull('verified_at')
            ->delete();

        $otp = $this->generateOtp();
        $expiresAt = $now->copy()->addMinutes(self::OTP_EXPIRE_MINUTES);

        DB::table('email_otps')->insert([
            'email' => $email,
            'otp_hash' => Hash::make($otp),
            'expires_at' => $expiresAt,
            'verified_at' => null,
            'attempt_count' => 0,
            'sent_count' => 1,
            'last_sent_at' => $now,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        // Hit rate limit (TTL 1 jam)
        RateLimiter::hit($rateKey, 3600);

        $expiresText = $expiresAt->format('d M Y H:i');
        Mail::to($email)->send(new OtpCodeMail($otp, $email, $expiresText));

        return redirect()->route('register.verify', ['email' => $email])->with('flash', [
            'type' => 'success',
            'message' => 'OTP berhasil dikirim. Cek email kamu.',
        ]);
    }

    public function showVerifyForm(Request $request)
    {
        $email = Str::lower(trim((string) $request->query('email')));

        if (!$email || !$this->isGmail($email)) {
            return redirect()->route('register.start');
        }

        $otpRow = DB::table('email_otps')
            ->where('email', $email)
            ->whereNull('verified_at')
            ->orderByDesc('otp_id')
            ->first();

        if (!$otpRow) {
            return redirect()->route('register.start')->with('flash', [
                'type' => 'info',
                'message' => 'Silakan minta OTP terlebih dahulu.',
            ]);
        }

        return Inertia::render('Auth/RegisterVerifyOtp', [
            'email' => $email,
            'expiresAt' => Carbon::parse($otpRow->expires_at)->toIso8601String(),
            'serverNow' => Carbon::now()->toIso8601String(),
        ]);
    }

    public function verifyOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'otp' => ['required', 'digits:6'],
        ]);

        $email = Str::lower(trim((string) $request->input('email')));
        $otp = trim((string) $request->input('otp'));

        if (!$this->isGmail($email)) {
            return back()->withErrors(['otp' => 'Email harus @gmail.com.']);
        }

        $otpRow = DB::table('email_otps')
            ->where('email', $email)
            ->whereNull('verified_at')
            ->orderByDesc('otp_id')
            ->first();

        if (!$otpRow) {
            return redirect()->route('register.start')->with('flash', [
                'type' => 'info',
                'message' => 'OTP tidak ditemukan. Silakan minta OTP lagi.',
            ]);
        }

        $now = Carbon::now();

        if ($now->greaterThan(Carbon::parse($otpRow->expires_at))) {
            return back()->withErrors(['otp' => 'OTP sudah kadaluwarsa. Silakan kirim ulang OTP.']);
        }

        if ((int) $otpRow->attempt_count >= self::OTP_MAX_ATTEMPTS) {
            return back()->withErrors(['otp' => 'Terlalu banyak percobaan. Silakan kirim ulang OTP.']);
        }

        // Naikkan attempt dulu (anti brute force)
        DB::table('email_otps')->where('otp_id', $otpRow->otp_id)->update([
            'attempt_count' => (int) $otpRow->attempt_count + 1,
            'updated_at' => $now,
        ]);

        $ok = Hash::check($otp, $otpRow->otp_hash);
        if (!$ok) {
            return back()->withErrors(['otp' => 'Kode OTP salah.']);
        }

        DB::table('email_otps')->where('otp_id', $otpRow->otp_id)->update([
            'verified_at' => $now,
            'updated_at' => $now,
        ]);

        // Kunci session untuk step set password
        $request->session()->put('register_verified_email', $email);
        $request->session()->put('register_verified_at', $now->toIso8601String());

        return redirect()->route('register.password')->with('flash', [
            'type' => 'success',
            'message' => 'Email berhasil diverifikasi. Silakan buat password.',
        ]);
    }

    public function resendOtp(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        $email = Str::lower(trim((string) $request->input('email')));

        if (!$this->isGmail($email)) {
            return back()->withErrors(['otp' => 'Email harus @gmail.com.']);
        }

        $otpRow = DB::table('email_otps')
            ->where('email', $email)
            ->whereNull('verified_at')
            ->orderByDesc('otp_id')
            ->first();

        if (!$otpRow) {
            return redirect()->route('register.start')->with('flash', [
                'type' => 'info',
                'message' => 'Silakan minta OTP terlebih dahulu.',
            ]);
        }

        $now = Carbon::now();
        $lastSent = $otpRow->last_sent_at ? Carbon::parse($otpRow->last_sent_at) : null;

        // Cooldown resend
        if ($lastSent && $now->diffInSeconds($lastSent) < self::RESEND_COOLDOWN_SECONDS) {
            $wait = self::RESEND_COOLDOWN_SECONDS - $now->diffInSeconds($lastSent);
            return back()->withErrors(['otp' => "Tunggu {$wait} detik untuk kirim ulang."]);
        }

        // Hourly limit resend/requests
        $rateKey = $this->sendRateKey($email, (string) $request->ip());
        if (RateLimiter::tooManyAttempts($rateKey, self::SEND_LIMIT_PER_HOUR)) {
            $seconds = RateLimiter::availableIn($rateKey);
            return back()->withErrors([
                'otp' => "Terlalu sering meminta OTP. Coba lagi dalam {$seconds} detik.",
            ]);
        }

        // Hapus OTP lama, buat yang baru
        DB::table('email_otps')->where('otp_id', $otpRow->otp_id)->delete();

        $otp = $this->generateOtp();
        $expiresAt = $now->copy()->addMinutes(self::OTP_EXPIRE_MINUTES);

        DB::table('email_otps')->insert([
            'email' => $email,
            'otp_hash' => Hash::make($otp),
            'expires_at' => $expiresAt,
            'verified_at' => null,
            'attempt_count' => 0,
            'sent_count' => ((int) $otpRow->sent_count) + 1,
            'last_sent_at' => $now,
            'ip_address' => $request->ip(),
            'user_agent' => substr((string) $request->userAgent(), 0, 1000),
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        RateLimiter::hit($rateKey, 3600);

        $expiresText = $expiresAt->format('d M Y H:i');
        Mail::to($email)->send(new OtpCodeMail($otp, $email, $expiresText));

        return redirect()->route('register.verify', ['email' => $email])->with('flash', [
            'type' => 'success',
            'message' => 'OTP baru berhasil dikirim.',
        ]);
    }

    public function showSetPassword(Request $request)
    {
        $email = (string) $request->session()->get('register_verified_email');

        if (!$email) {
            return redirect()->route('register.start')->with('flash', [
                'type' => 'info',
                'message' => 'Silakan verifikasi email terlebih dahulu.',
            ]);
        }

        return Inertia::render('Auth/RegisterSetPassword', [
            'email' => $email,
        ]);
    }

    public function complete(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'name' => ['required', 'string', 'min:2', 'max:80'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $email = Str::lower(trim((string) $request->input('email')));
        $sessionEmail = (string) $request->session()->get('register_verified_email');

        if (!$sessionEmail || $sessionEmail !== $email) {
            return redirect()->route('register.start')->with('flash', [
                'type' => 'error',
                'message' => 'Sesi verifikasi tidak valid. Silakan ulang verifikasi OTP.',
            ]);
        }

        if (!$this->isGmail($email)) {
            return back()->withErrors(['email' => 'Email harus @gmail.com.']);
        }

        $now = Carbon::now();

        DB::beginTransaction();
        try {
            $user = User::where('email', $email)->first();

            if (!$user) {
                $user = new User();
                $user->email = $email;
            }

            $user->name = $request->input('name');
            $user->password = Hash::make((string) $request->input('password'));
            $user->email_verified_at = $now;
            $user->is_active = true;
            $user->save();

            Role::firstOrCreate(['name' => 'user', 'guard_name' => 'web']);
            if (!$user->hasRole('user')) {
                $user->assignRole('user');
            }

            $request->session()->forget(['register_verified_email', 'register_verified_at']);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['password' => 'Terjadi error saat menyimpan akun. Coba lagi.']);
        }

        // aman: kalau route name belum ada, tetap bisa redirect
        return $this->redirectToLogin()->with('flash', [
            'type' => 'success',
            'message' => 'Registrasi sukses. Silakan login.',
        ]);
    }

    private function generateOtp(): string
    {
        // 6 digit (100000 - 999999)
        $min = (int) str_pad('1', self::OTP_LENGTH, '0');
        $max = (int) str_repeat('9', self::OTP_LENGTH);
        return (string) random_int($min, $max);
    }

    private function isGmail(string $email): bool
    {
        return Str::endsWith($email, '@gmail.com');
    }

    private function sendRateKey(string $email, string $ip): string
    {
        return 'otp-send|' . $email . '|' . $ip;
    }

    private function redirectToLogin()
    {
        if (Route::has('user.login')) {
            return redirect()->route('user.login');
        }

        if (Route::has('login')) {
            return redirect()->route('login');
        }

        return redirect('/login');
    }
}
