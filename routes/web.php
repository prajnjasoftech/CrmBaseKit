<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Guest routes (not authenticated)
Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    Route::get('/forgot-password', [PasswordResetController::class, 'showForgotPassword'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetController::class, 'sendResetLink'])->name('password.email');

    Route::get('/reset-password/{token}', [PasswordResetController::class, 'showResetPassword'])->name('password.reset');
    Route::post('/reset-password', [PasswordResetController::class, 'resetPassword'])->name('password.update');
});

// Authenticated routes
Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Email verification
    Route::get('/email/verify', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verify/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/verification-notification', [EmailVerificationController::class, 'resend'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    // Dashboard
    Route::get('/', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');

    // Business routes
    Route::resource('businesses', BusinessController::class);

    // User Management routes
    Route::resource('users', UserController::class);

    // Lead routes
    Route::get('leads/{lead}/convert', [LeadController::class, 'showConvert'])->name('leads.convert');
    Route::post('leads/{lead}/convert', [LeadController::class, 'convert'])->name('leads.convert.store');
    Route::resource('leads', LeadController::class);

    // Customer routes
    Route::resource('customers', CustomerController::class);
});
