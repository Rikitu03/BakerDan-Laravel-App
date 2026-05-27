<?php

use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Models\Review;
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
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\GeminiChatController;
use App\Http\Controllers\Api\MessageController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\PayMongoWebhookController;

/*
|--------------------------------------------------------------------------
| Web Routes (Public Pages)
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    $topProducts = Product::query()
        ->select([
            'inventory_products.id',
            'inventory_products.product_name',
            'inventory_products.description',
            'inventory_products.price',
            'inventory_products.image_url',
        ])
        ->selectRaw('COALESCE(SUM(order_items.quantity), 0) as quantity_sold')
        ->selectRaw('COALESCE(SUM(order_items.quantity * order_items.price), 0) as revenue')
        ->join('order_items', 'order_items.product_id', '=', 'inventory_products.id')
        ->join('orders', 'orders.id', '=', 'order_items.order_id')
        ->where('inventory_products.is_active', true)
        ->where('orders.status', 'delivered')
        ->where('orders.payment_status', 'paid')
        ->groupBy(
            'inventory_products.id',
            'inventory_products.product_name',
            'inventory_products.description',
            'inventory_products.price',
            'inventory_products.image_url'
        )
        ->orderByDesc('quantity_sold')
        ->orderBy('inventory_products.product_name')
        ->take(4)
        ->get();

    if ($topProducts->count() < 4) {
        $existingIds = $topProducts->pluck('id')->all();

        $fallbackProducts = Product::query()
            ->where('is_active', true)
            ->whereNotIn('id', $existingIds)
            ->orderBy('product_name')
            ->take(4 - $topProducts->count())
            ->get()
            ->map(function (Product $product) {
                $product->setAttribute('quantity_sold', 0);
                $product->setAttribute('revenue', 0);

                return $product;
            });

        $topProducts = $topProducts->concat($fallbackProducts)->values();
    }

    $formatProduct = function (Product $product): array {
        return [
            'title' => $product->product_name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'imagePath' => $product->image_url ? ltrim((string) $product->image_url, '/') : null,
            'salesCount' => (int) ($product->quantity_sold ?? 0),
            'revenue' => (float) ($product->revenue ?? 0),
        ];
    };

    $featured = $topProducts->first() ? $formatProduct($topProducts->first()) : [
        'title' => 'Korean Garlic Cream Cheese Bun',
        'description' => 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
        'price' => 395.00,
        'imagePath' => 'images/bakerdan/bread/Creme_Cheese_Garlic.png',
        'salesCount' => 0,
        'revenue' => 0,
    ];

    $bestSellers = $topProducts
        ->skip(1)
        ->take(3)
        ->map($formatProduct)
        ->values()
        ->all();

    $reviewPool = Review::query()
        ->with(['user.detail', 'product'])
        ->whereNotNull('comment')
        ->where('comment', '!=', '')
        ->orderByDesc('created_at')
        ->take(8)
        ->get();

    $reviews = $reviewPool
        ->shuffle()
        ->take(3)
        ->map(function (Review $review): array {
            $customerName = $review->user?->detail?->name
                ?? $review->user?->detail?->username
                ?? 'Verified customer';

            return [
                'quote' => $review->comment,
                'name' => $customerName,
                'role' => $review->product?->product_name ? $review->product->product_name : 'Customer review',
                'rating' => max(1, min(5, (int) $review->rating)),
            ];
        })
        ->values()
        ->all();

    if (count($reviews) < 3) {
        $reviews = array_merge($reviews, [
            [
                'quote' => 'Fresh bread, consistent quality, and easy ordering for bulk requests.',
                'name' => 'BakerDan Customer',
                'role' => 'Customer review',
                'rating' => 5,
            ],
            [
                'quote' => 'Great presentation and generous portions for events and office orders.',
                'name' => 'BakerDan Customer',
                'role' => 'Customer review',
                'rating' => 5,
            ],
            [
                'quote' => 'Very reliable for repeat orders and always delivered fresh.',
                'name' => 'BakerDan Customer',
                'role' => 'Customer review',
                'rating' => 5,
            ],
        ]);

        $reviews = array_slice($reviews, 0, 3);
    }

    return view('welcome', [
        'featured' => $featured,
        'bestSellers' => $bestSellers,
        'reviews' => $reviews,
    ]);
});

Route::post('/webhooks/paymongo', [PayMongoWebhookController::class, 'handle']);

Route::get('/login', [LoginController::class, 'show'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/login/guest', [LoginController::class, 'guest'])->name('login.guest');
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

Route::get('/cart', fn() => redirect('/customer/cart'))->name('cart');

Route::post('/customer/cart/add/{productId}', [CartController::class, 'addAndRedirect'])
    ->name('customer.cart.add');

// All customer routes return the SPA. Guest-only access is gated inside the SPA.
Route::get('/customer/{any?}', [CustomerController::class, 'spa'])
    ->where('any', '.*')
    ->name('customer.home');

/*
|--------------------------------------------------------------------------
| API Routes for Vue SPA
|--------------------------------------------------------------------------
| These endpoints are called by the Vue frontend via Axios
*/

/*
|--------------------------------------------------------------------------
| Admin Routes (placeholder)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.home');
    Route::post('/inventory', [AdminController::class, 'storeInventory'])->name('admin.inventory.store');
    Route::post('/inventory/bulk', [AdminController::class, 'bulkUploadInventory'])->name('admin.inventory.bulk');
    Route::put('/inventory/{product}', [AdminController::class, 'updateInventory'])->name('admin.inventory.update');
    Route::delete('/inventory/{product}', [AdminController::class, 'destroyInventory'])->name('admin.inventory.destroy');

    // Promos & Discounts
    Route::post('/promos', [AdminController::class, 'storePromo'])->name('admin.promos.store');
    Route::put('/promos/{promo}', [AdminController::class, 'updatePromo'])->name('admin.promos.update');
    Route::delete('/promos/{promo}', [AdminController::class, 'destroyPromo'])->name('admin.promos.destroy');

    Route::post('/orders/walkin', [AdminController::class, 'storeWalkinOrder'])->name('admin.orders.walkin');
    Route::patch('/orders/{order}/status', [AdminController::class, 'updateOrderStatus'])->name('admin.orders.status');
    Route::patch('/orders/{order}/payment-status', [AdminController::class, 'updateOrderPaymentStatus'])->name('admin.orders.payment-status');
    Route::patch('/users/{user}/status', [AdminController::class, 'toggleUserStatus'])->name('admin.users.status');
});
