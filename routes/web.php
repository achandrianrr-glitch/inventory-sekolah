<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\UserLoginController;
use App\Http\Controllers\Auth\AdminLoginController;
use App\Http\Controllers\Auth\RegisterOtpController;
use App\Http\Controllers\Auth\SocialAuthController;
use Inertia\Inertia;

// =====================
// USER PORTAL (AUTH + USER ROLE)
// =====================
Route::middleware(['auth', 'user.role'])->group(function () {
    Route::get('/', function () {
        return Inertia::render('User/Home');
    })->name('user.home');
});

// =====================
// GUEST ROUTES (USER)
// =====================
Route::middleware('guest')->group(function () {

    /**
     * IMPORTANT:
     * Laravel default mengasumsikan route name "login" ada.
     * Jadi kita pakai name "login" untuk login user.
     */
    Route::get('/login', [UserLoginController::class, 'show'])->name('login');
    Route::post('/login', [UserLoginController::class, 'login'])->name('login.submit');

    // REGISTER (Gmail + OTP)
    Route::get('/register', [RegisterOtpController::class, 'showEmailForm'])->name('register.start');
    Route::post('/register/request-otp', [RegisterOtpController::class, 'requestOtp'])->name('register.requestOtp');

    Route::get('/register/verify', [RegisterOtpController::class, 'showVerifyForm'])->name('register.verify');
    Route::post('/register/verify', [RegisterOtpController::class, 'verifyOtp'])->name('register.verify.submit');
    Route::post('/register/resend', [RegisterOtpController::class, 'resendOtp'])->name('register.resend');

    Route::get('/register/set-password', [RegisterOtpController::class, 'showSetPassword'])->name('register.password');
    Route::post('/register/complete', [RegisterOtpController::class, 'complete'])->name('register.complete');

    // SOCIAL LOGIN
    Route::get('/auth/{provider}/redirect', [SocialAuthController::class, 'redirect'])->name('social.redirect');
    Route::get('/auth/{provider}/callback', [SocialAuthController::class, 'callback'])->name('social.callback');
});

// =====================
// LOGOUT USER (hanya untuk portal user)
// =====================
Route::post('/logout', [UserLoginController::class, 'logout'])
    ->name('logout')
    ->middleware('auth');

// =====================
// ADMIN AUTH + ADMIN PORTAL (AUTH + ADMIN ROLE)
// =====================
Route::prefix('admin')->group(function () {

    // ADMIN LOGIN (guest)
    Route::middleware('guest')->group(function () {
        Route::get('/login', [AdminLoginController::class, 'show'])->name('admin.login');
        Route::post('/login', [AdminLoginController::class, 'login'])->name('admin.login.submit');
    });

    // ✅ LOGOUT ADMIN (route khusus admin)
   Route::post('/logout', [AdminLoginController::class, 'logout'])
    ->name('admin.logout')
    ->middleware('auth');


    // ADMIN PORTAL (auth + admin role)
    Route::middleware(['auth', 'admin.role'])->group(function () {
        Route::get('/', function () {
            return Inertia::render('Admin/Dashboard');
        })->name('admin.dashboard');
    });
});
