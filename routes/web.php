<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ForgotPasswordController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

Route::prefix('register')->group(function () {
    Route::get('/step-1', [RegisterController::class, 'showStep1'])->name('register.step1');
    Route::post('/step-1', [RegisterController::class, 'handleStep1']);
    
    Route::get('/step-2', [RegisterController::class, 'showStep2'])->name('register.step2');
    Route::post('/step-2', [RegisterController::class, 'handleStep2']);
    Route::post('/resend-otp', [RegisterController::class, 'resendOtp'])->name('register.resend-otp');

    Route::get('/step-3', [RegisterController::class, 'showStep3'])->name('register.step3');
    Route::post('/step-3', [RegisterController::class, 'handleStep3']);
});

Route::prefix('forgot-password')->group(function () {
    Route::get('/step-1', [ForgotPasswordController::class, 'showStep1'])->name('password.request');
    Route::post('/step-1', [ForgotPasswordController::class, 'handleStep1']);
    
    Route::get('/step-2', [ForgotPasswordController::class, 'showStep2'])->name('password.otp');
    Route::post('/step-2', [ForgotPasswordController::class, 'handleStep2']);
    
    Route::get('/step-3', [ForgotPasswordController::class, 'showStep3'])->name('password.reset');
    Route::post('/step-3', [ForgotPasswordController::class, 'handleStep3']);
});

Route::get('/admin', function() { return view('admin.dashboard'); })->name('admin.home');

// BAKERDAN customer SPA entry points.
Route::view('/customer', 'customer.app')->name('customer.home');
Route::view('/customer/cart', 'customer.app')->name('customer.cart');
Route::view('/customer/customize', 'customer.app')->name('customer.customize');
Route::view('/customer/notifications', 'customer.app')->name('customer.notifications');
Route::view('/customer/settings', 'customer.app')->name('customer.settings');

// Catch-all route for any additional nested customer SPA paths.
Route::get('/customer/{any?}', function () {
    return view('customer.app');
})->where('any', '.*')->name('customer.app');