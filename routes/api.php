<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CustomOrderController;
use App\Http\Controllers\Api\CustomerOrderController;
use App\Http\Controllers\Api\OrderPaymentController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\GeminiChatController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ReviewController;

// Default Sanctum User Route
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// --- Public API Routes ---
Route::post('/chat', [GeminiChatController::class, 'chat']);

Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [ProductController::class, 'categories']);
Route::get('/products/{id}/reviews', [ReviewController::class, 'index']);

// Cart (Public/Guest)
Route::middleware([\Illuminate\Session\Middleware\StartSession::class])->group(function () {
    Route::get('/cart', [CartController::class, 'index']);
    Route::post('/cart/add/{productId}', [CartController::class, 'add']);
    Route::post('/cart/custom', [CartController::class, 'addCustom']);
    Route::put('/cart/items/{itemId}', [CartController::class, 'update']);
    Route::delete('/cart/items/{itemId}', [CartController::class, 'remove']);
    Route::delete('/cart/clear', [CartController::class, 'clear']);
});

// --- Protected API Routes (All Authenticated Users) ---
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{notificationId}/read', [NotificationController::class, 'markRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead']);
    Route::delete('/notifications/{notificationId}', [NotificationController::class, 'destroy']);
    Route::delete('/notifications', [NotificationController::class, 'destroyAll']);

    // Shared Messaging
    Route::get('/conversations', [MessageController::class, 'getConversations']);
    Route::get('/conversations/{id}/messages', [MessageController::class, 'getMessages']);
    Route::post('/conversations/{id}/read', [MessageController::class, 'markAsRead']);
    Route::post('/messages', [MessageController::class, 'sendMessage']);
});

// --- Protected API Routes (Customers Only) ---
Route::middleware(['auth:sanctum', 'role:customer'])->group(function () {
    // Checkout
    Route::post('/checkout', [CartController::class, 'checkout']);
    Route::get('/promos/active', [CartController::class, 'getActivePromos']);
    Route::post('/promos/validate', [CartController::class, 'validatePromoCode']);
    Route::get('/orders', [CustomerOrderController::class, 'index']);
    Route::get('/purchases', [CustomerOrderController::class, 'purchaseHistory']);
    Route::patch('/orders/{order}/cancel', [CustomerOrderController::class, 'cancel']);
    Route::get('/orders/{order}/payment-status', [OrderPaymentController::class, 'show']);
    Route::patch('/pending-orders/{pendingOrderId}/cancel', [CustomerOrderController::class, 'cancelPending']);
    Route::get('/pending-orders/{pendingOrderId}/payment-status', [OrderPaymentController::class, 'showPending']);

    // Custom Orders & Profile
    Route::post('/custom-orders', [CustomOrderController::class, 'store']);
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::put('/profile/password', [ProfileController::class, 'updatePassword']);
    Route::post('/profile/verify-password', [ProfileController::class, 'verifyCurrentPassword']);

    // Reviews
    Route::post('/reviews', [ReviewController::class, 'store']);
    Route::get('/reviews/customer', [ReviewController::class, 'customerReviews']);
});
