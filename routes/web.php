<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CustomOrderController;
use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\OrderPaymentController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\PayMongoWebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes (Public Pages)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return view('welcome');
});

Route::post('/webhooks/paymongo', [PayMongoWebhookController::class, 'handle']);

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

/*
|--------------------------------------------------------------------------
| Customer SPA Routes
|--------------------------------------------------------------------------
| All customer routes serve the same Vue SPA view
| Vue Router handles client-side navigation
*/

Route::middleware(['auth', 'role:customer'])->group(function () {
    // All customer routes return the SPA
    Route::get('/customer/{any?}', [CustomerController::class, 'spa'])
        ->where('any', '.*')
        ->name('customer.home');
});

/*
|--------------------------------------------------------------------------
| API Routes for Vue SPA
|--------------------------------------------------------------------------
| These endpoints are called by the Vue frontend via Axios
*/

Route::prefix('api')->middleware(['auth', 'role:customer'])->group(function () {
    
    // Products
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{id}', [ProductController::class, 'show']);
    
    // Categories
    Route::get('/categories', [ProductController::class, 'categories']);
    
    // Cart
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add/{productId}', [CartController::class, 'add']);
    Route::post('/cart/custom', [CartController::class, 'addCustom']);
    Route::put('/cart/items/{itemId}', [CartController::class, 'update']);
    Route::delete('/cart/items/{itemId}', [CartController::class, 'remove']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
    Route::post('/checkout', [CartController::class, 'checkout']);
    Route::get('/orders', [CustomerOrderController::class, 'index']);
    Route::patch('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel']);
    Route::get('/orders/{order}/payment-status', [OrderPaymentController::class, 'show']);
    
    // Custom Orders
    Route::post('/custom-orders', [CustomOrderController::class, 'store']);
    
    // Profile
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
});

/*
|--------------------------------------------------------------------------
| Admin Routes (placeholder)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.home');
    Route::post('/inventory', [AdminController::class, 'storeInventory'])->name('admin.inventory.store');
    Route::put('/inventory/{product}', [AdminController::class, 'updateInventory'])->name('admin.inventory.update');
    Route::delete('/inventory/{product}', [AdminController::class, 'destroyInventory'])->name('admin.inventory.destroy');
    Route::patch('/orders/{order}/status', [AdminController::class, 'updateOrderStatus'])->name('admin.orders.status');
    Route::patch('/orders/{order}/payment-status', [AdminController::class, 'updateOrderPaymentStatus'])->name('admin.orders.payment-status');
});
