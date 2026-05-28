<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\CustomerNotification;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Promo;
use App\Services\CustomOrderImageService;
use App\Services\GuestCartService;
use App\Services\PendingOrderService;
use App\Services\PayMongoService;
use App\Services\ProductOptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class CartController extends Controller
{
    private const SHIPPING_FEE = 150.00;
    private const PICKUP_SHOP_NAME = 'Bakerdan Shop';
    private const PICKUP_SHOP_ADDRESS = 'Bakerdan Shop, 28 Market Avenue, San Nicolas, Pasig City, Metro Manila';
    private const PICKUP_SHOP_PIN_LINK = 'https://maps.app.goo.gl/bakerdan-pickup-placeholder';

    public function __construct(
        private ProductOptionService $productOptions,
        private GuestCartService $guestCart,
    )
    {
    }

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => Auth::check()
                ? $this->cartSummary($this->userCart())
                : $this->guestCartSummary($request),
        ]);
    }

    public function add(Request $request, int $productId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:99',
            'option_id' => 'nullable|string|max:120',
            'include_cart' => 'nullable|boolean',
            'size' => 'nullable|string|max:100',
            'flavor' => 'nullable|string|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $product = Product::query()
            ->whereKey($productId)
            ->where('is_active', true)
            ->firstOrFail();

        if ($product->category === 'Cakes') {
            return response()->json([
                'success' => false,
                'message' => 'Cake products are made-to-order only. Please use the cake ordering guide to submit a custom request.',
            ], 422);
        }

        $validated = $validator->validated();
        $option = $this->productOptions->optionFor($product, $validated['option_id'] ?? null);

        if (filled($validated['option_id'] ?? null) && !$option) {
            return response()->json([
                'success' => false,
                'message' => 'Please choose a valid option for this product.',
            ], 422);
        }

        $minimumQuantity = (int) ($option['minimum_quantity'] ?? 1);
        if ((int) $validated['quantity'] < $minimumQuantity) {
            return response()->json([
                'success' => false,
                'message' => "This option requires a minimum quantity of {$minimumQuantity}.",
            ], 422);
        }

        $size = $option['size'] ?? ($validated['size'] ?? null);
        $flavor = $option['flavor'] ?? ($validated['flavor'] ?? null);
        $unitPrice = (float) ($option['price'] ?? $product->price);

        if (! Auth::check()) {
            $cartItem = $this->guestCart->addCatalogItem($request, $product, $validated, $option);
            $includeCart = $request->boolean('include_cart', true);
            $data = [
                'item' => $this->serializeGuestCartItem($cartItem),
            ];

            if ($includeCart) {
                $data['cart'] = $this->guestCartSummary($request);
            }

            return response()->json([
                'success' => true,
                'message' => 'Item added to cart successfully.',
                'data' => $data,
            ]);
        }

        $cart = $this->userCart(true);

        $cartItem = DB::transaction(function () use ($cart, $product, $validated, $size, $flavor, $unitPrice): CartItem {
            $existingItem = $cart->items()
                ->where('item_type', 'catalog')
                ->where('product_id', $product->id)
                ->when($size !== null, fn ($query) => $query->where('size', $size), fn ($query) => $query->whereNull('size'))
                ->when($flavor !== null, fn ($query) => $query->where('flavor', $flavor), fn ($query) => $query->whereNull('flavor'))
                ->first();

            if ($existingItem) {
                $existingItem->increment('quantity', (int) $validated['quantity']);
                return $existingItem->fresh(['product']);
            }

            return $cart->items()->create([
                'product_id' => $product->id,
                'item_type' => 'catalog',
                'quantity' => (int) $validated['quantity'],
                'unit_price' => $unitPrice,
                'product_name' => $product->product_name,
                'description' => $product->description,
                'image_url' => $product->image_url,
                'size' => $size,
                'flavor' => $flavor,
            ])->load('product');
        });

        $includeCart = $request->boolean('include_cart', true);
        $data = [
            'item' => $this->serializeCartItem($cartItem),
        ];

        if ($includeCart) {
            $data['cart'] = $this->cartSummary($cart->fresh());
        }

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully.',
            'data' => $data,
        ]);
    }

    public function addAndRedirect(Request $request, int $productId): RedirectResponse
    {
        $returnTo = $this->safeCustomerReturnPath((string) $request->input('return_to', '/customer/cart'));
        $request->merge(['include_cart' => false]);

        try {
            $response = $this->add($request, $productId);
            $payload = $response->getData(true);

            if (! $response->isSuccessful()) {
                return redirect($returnTo)->with('cart_error', $this->cartErrorMessage($payload));
            }

            $itemId = data_get($payload, 'data.item.id');

            if (! $itemId) {
                return redirect($returnTo)->with('cart_error', 'The item was added, but the cart could not identify it.');
            }

            return redirect('/customer/cart?added=' . urlencode((string) $itemId));
        } catch (Throwable $exception) {
            report($exception);

            return redirect($returnTo)->with('cart_error', 'Unable to add this item to the cart right now.');
        }
    }

    public function addCustom(Request $request, CustomOrderImageService $customOrderImageService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'size' => 'required|string|max:120',
            'quantity' => 'required|integer|min:1|max:99',
            'flavor' => 'nullable|string|max:120',
            'design_description' => 'nullable|string|max:600',
            'dedication_message' => 'nullable|string|max:300',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'image_url' => 'nullable|string|max:2048',
            'guide_section' => 'nullable|string|max:120',
            'product_name' => 'nullable|string|max:255',
            'base_price' => 'nullable|numeric|min:0',
        ]);

        $validator->after(function ($validator) use ($request): void {
            if (! $this->hasCustomDesignInput($request)) {
                $validator->errors()->add('design_description', 'Please upload a reference image or describe the custom design.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $imagePath = $validated['image_url'] ?? null;

        if ($request->hasFile('image')) {
            $imagePath = $customOrderImageService->storeReferenceImage($request->file('image'));
        }

        $cartItemPayload = [
            'product_id' => null,
            'item_type' => 'custom',
            'quantity' => (int) $validated['quantity'],
            'unit_price' => $this->customUnitPrice($validated['size'], isset($validated['base_price']) ? (float) $validated['base_price'] : null),
            'product_name' => $validated['product_name'] ?? 'Custom Celebration Order',
            'description' => $this->customDescription(
                $validated['size'],
                $validated['flavor'] ?? null,
                $validated['product_name'] ?? null,
                $validated['guide_section'] ?? null,
            ),
            'image_url' => $imagePath,
            'design_description' => $validated['design_description'] ?? null,
            'dedication_message' => $validated['dedication_message'] ?? null,
            'size' => $validated['size'],
            'flavor' => $validated['flavor'] ?? null,
        ];

        if (! Auth::check()) {
            $cartItem = $this->guestCart->addCustomItem($request, $cartItemPayload);

            return response()->json([
                'success' => true,
                'message' => 'Custom item added to cart successfully.',
                'data' => [
                    'item' => $this->serializeGuestCartItem($cartItem),
                    'cart' => $this->guestCartSummary($request),
                ],
            ]);
        }

        $cart = $this->userCart(true);
        $cartItem = $cart->items()->create($cartItemPayload);

        return response()->json([
            'success' => true,
            'message' => 'Custom item added to cart successfully.',
            'data' => [
                'item' => $this->serializeCartItem($cartItem),
                'cart' => $this->cartSummary($cart->fresh()),
            ],
        ]);
    }

    public function update(Request $request, int $itemId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        if (! Auth::check()) {
            $cartItem = $this->guestCart->updateQuantity($request, $itemId, (int) $validator->validated()['quantity']);

            if (! $cartItem) {
                abort(404, 'Cart item not found.');
            }

            return response()->json([
                'success' => true,
                'message' => 'Cart updated successfully.',
                'data' => [
                    'item' => $this->serializeGuestCartItem($cartItem),
                    'cart' => $this->guestCartSummary($request),
                ],
            ]);
        }

        $cartItem = $this->ownedCartItem($itemId);
        $cartItem->update([
            'quantity' => (int) $validator->validated()['quantity'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully.',
            'data' => [
                'item' => $this->serializeCartItem($cartItem->fresh(['product'])),
                'cart' => $this->cartSummary($cartItem->cart->fresh()),
            ],
        ]);
    }

    public function remove(Request $request, int $itemId): JsonResponse
    {
        if (! Auth::check()) {
            $removed = $this->guestCart->removeItem($request, $itemId);

            if (! $removed) {
                abort(404, 'Cart item not found.');
            }

            return response()->json([
                'success' => true,
                'message' => 'Item removed from cart.',
                'data' => [
                    'cart' => $this->guestCartSummary($request),
                ],
            ]);
        }

        $cartItem = $this->ownedCartItem($itemId);
        $cart = $cartItem->cart;
        CartItem::destroy($itemId);

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
            'data' => [
                'cart' => $this->cartSummary($cart->fresh()),
            ],
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        if (! Auth::check()) {
            $this->guestCart->clear($request);

            return response()->json([
                'success' => true,
                'message' => 'Cart cleared successfully.',
                'data' => [
                    'cart' => $this->guestCartSummary($request),
                ],
            ]);
        }

        $cart = $this->userCart();

        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully.',
            'data' => [
                'cart' => $this->cartSummary($cart?->fresh()),
            ],
        ]);
    }

    public function checkout(Request $request, PayMongoService $payMongo, PendingOrderService $pendingOrders): JsonResponse
    {
        if (! Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Please log in or create an account before checkout.',
                'auth_required' => true,
            ], 401);
        }

        $validator = Validator::make($request->all(), [
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'integer',
            'payment_method' => 'required|string|in:gcash,maya',
            'fulfillment_method' => 'nullable|string|in:delivery,pickup',
            'shipping_address' => 'nullable|string|max:1000',
            'shipping_pin_link' => 'nullable|string|max:1000',
            'promo_code' => 'nullable|string|max:100',
        ]);

        $validator->after(function ($validator) use ($request): void {
            $fulfillmentMethod = $request->input('fulfillment_method', 'delivery');

            if ($fulfillmentMethod === 'delivery' && blank($request->input('shipping_address'))) {
                $validator->errors()->add('shipping_address', 'A delivery address is required for delivery orders.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $fulfillmentMethod = $validated['fulfillment_method'] ?? 'delivery';
        $cart = $this->userCart();

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Your cart is empty.',
            ], 422);
        }

        $itemIds = collect($validated['item_ids'])->map(fn ($id) => (int) $id)->unique()->values();
        $items = $cart->items()
            ->with('product')
            ->whereIn('id', $itemIds)
            ->get();

        if ($items->count() !== $itemIds->count()) {
            return response()->json([
                'success' => false,
                'message' => 'Some selected cart items could not be found.',
            ], 422);
        }

        $itemsSubtotal = $items->sum(fn (CartItem $item) => $this->cartItemUnitPrice($item) * $item->quantity);
        $shippingFee = $this->shippingFeeFor($fulfillmentMethod, $items->isNotEmpty());

        $promoId = null;
        $discountAmount = 0.00;
        $promoCode = isset($validated['promo_code']) ? strtoupper(trim($validated['promo_code'])) : null;

        if ($promoCode) {
            $promo = Promo::where('code', $promoCode)->first();
            if (!$promo) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid promo code.',
                ], 422);
            }

            $result = $this->calculatePromoDiscount($promo, $itemIds->all(), (int) Auth::id());
            if (!$result['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => $result['message'],
                ], 422);
            }

            $promoId = $promo->id;
            $discountAmount = $result['discount_amount'];
        }

        $pendingOrder = null;
        try {
            $expiresAt = now()->addHours(2);
            $pendingOrder = $pendingOrders->create([
                'user_id' => (int) Auth::id(),
                'total_amount' => max(0.00, $itemsSubtotal + $shippingFee - $discountAmount),
                'promo_id' => $promoId,
                'discount_amount' => $discountAmount,
                'payment_method' => $validated['payment_method'],
                'payment_provider' => 'paymongo',
                'shipping_address' => $this->formatShippingAddress(
                    $fulfillmentMethod,
                    $validated['shipping_address'] ?? null,
                    $validated['shipping_pin_link'] ?? null,
                ),
                'expires_at' => $expiresAt,
                'items' => $items->map(function (CartItem $item): array {
                    return [
                        'product_id' => $item->product_id,
                        'item_type' => $item->item_type,
                        'quantity' => $item->quantity,
                        'unit_price' => $this->cartItemUnitPrice($item),
                        'product_name' => $this->cartItemName($item),
                        'description' => $this->cartItemDescription($item),
                        'image_url' => $item->item_type === 'custom' ? $item->image_url : $item->product?->image_url,
                        'design_description' => $item->design_description,
                        'dedication_message' => $item->dedication_message,
                        'size' => $item->size,
                        'flavor' => $item->flavor,
                    ];
                })->values()->all(),
            ]);

            $checkoutSession = $payMongo->createCheckoutSessionFromPendingOrder($pendingOrder);
            $pendingOrder = $pendingOrders->syncCheckoutSession($pendingOrder, $checkoutSession);

            DB::transaction(function () use ($items): void {
                $items->each->delete();
            });
        } catch (Throwable $exception) {
            report($exception);

            if ($pendingOrder) {
                $pendingOrders->forget((string) $pendingOrder['id']);
            }

            return response()->json([
                'success' => false,
                'message' => 'Unable to start the PayMongo checkout right now.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Checkout session created successfully.',
            'data' => [
                'order' => $pendingOrders->serializeForCustomer($pendingOrder),
                'cart' => $this->cartSummary($cart->fresh()),
            ],
        ]);
    }

    private function safeCustomerReturnPath(string $path): string
    {
        if (str_starts_with($path, '/customer/')) {
            return $path;
        }

        return '/customer/cart';
    }

    /**
     * @param array<string, mixed> $payload
     */
    private function cartErrorMessage(array $payload): string
    {
        if (filled($payload['message'] ?? null)) {
            return (string) $payload['message'];
        }

        $firstValidationError = collect($payload['errors'] ?? [])
            ->flatten()
            ->first();

        return $firstValidationError ?: 'Unable to add this item to the cart right now.';
    }

    private function userCart(bool $create = false): ?Cart
    {
        if ($create) {
            return Cart::query()->firstOrCreate([
                'user_id' => Auth::id(),
            ]);
        }

        return Cart::query()->where('user_id', Auth::id())->first();
    }

    private function ownedCartItem(int $itemId): CartItem
    {
        return CartItem::query()
            ->with(['cart', 'product'])
            ->whereKey($itemId)
            ->whereHas('cart', fn ($query) => $query->where('user_id', Auth::id()))
            ->firstOrFail();
    }

    private function cartSummary(?Cart $cart): array
    {
        if (!$cart) {
            return [
                'items' => [],
                'subtotal' => 0,
                'tax' => 0,
                'total' => 0,
                'item_count' => 0,
                'quantity_count' => 0,
            ];
        }

        $items = $cart->items()->with('product')->latest('id')->get();
        $serializedItems = $items->map(fn (CartItem $item): array => $this->serializeCartItem($item))->values();
        $subtotal = $serializedItems->sum('line_total');

        return [
            'items' => $serializedItems,
            'subtotal' => round($subtotal, 2),
            'tax' => 0,
            'total' => round($subtotal, 2),
            'item_count' => $serializedItems->count(),
            'quantity_count' => $serializedItems->sum('quantity'),
        ];
    }

    private function guestCartSummary(Request $request): array
    {
        $serializedItems = collect($this->guestCart->items($request))
            ->map(fn (array $item): array => $this->serializeGuestCartItem($item))
            ->sortByDesc('id')
            ->values();
        $subtotal = $serializedItems->sum('line_total');

        return [
            'items' => $serializedItems,
            'subtotal' => round($subtotal, 2),
            'tax' => 0,
            'total' => round($subtotal, 2),
            'item_count' => $serializedItems->count(),
            'quantity_count' => $serializedItems->sum('quantity'),
        ];
    }

    private function serializeCartItem(CartItem $item): array
    {
        $unitPrice = $this->cartItemUnitPrice($item);
        $selectedOption = $this->selectedCartItemOption($item);

        return [
            'basePrice' => $unitPrice,
            'id' => $item->id,
            'product_id' => $item->product_id,
            'optionId' => $selectedOption['id'] ?? null,
            'productName' => $this->cartItemName($item),
            'source' => $item->item_type === 'custom' ? 'custom' : 'catalog',
            'name' => $this->cartItemName($item),
            'description' => $this->cartItemDescription($item),
            'price' => $unitPrice,
            'quantity' => $item->quantity,
            'size' => $item->size,
            'flavor' => $item->flavor,
            'image' => $this->cartItemImageUrl($item),
            'tag' => $item->item_type === 'custom' ? 'Custom Order' : ($item->product?->is_active ? 'Available' : 'Unavailable'),
            'average_rating' => $item->product?->average_rating,
            'review_count' => $item->product?->review_count,
            'minimumQuantity' => (int) ($selectedOption['minimum_quantity'] ?? 1),
            'designDescription' => $item->design_description,
            'dedicationMessage' => $item->dedication_message,
            'line_total' => round($unitPrice * $item->quantity, 2),
        ];
    }

    /**
     * @param array<string, mixed> $item
     */
    private function serializeGuestCartItem(array $item): array
    {
        $product = isset($item['product_id'])
            ? Product::query()->whereKey($item['product_id'])->first()
            : null;
        $isCustom = ($item['item_type'] ?? 'catalog') === 'custom';
        $unitPrice = $isCustom
            ? (float) ($item['unit_price'] ?? 0)
            : (float) ($item['unit_price'] ?? $product?->price ?? 0);
        $quantity = (int) ($item['quantity'] ?? 1);
        $selectedOption = $product ? $this->selectedGuestCartItemOption($item, $product) : null;
        $name = $this->guestCartItemName($item, $product);

        return [
            'basePrice' => $unitPrice,
            'id' => (int) ($item['id'] ?? 0),
            'product_id' => $item['product_id'] ?? null,
            'optionId' => $selectedOption['id'] ?? null,
            'productName' => $name,
            'source' => $isCustom ? 'custom' : 'catalog',
            'name' => $name,
            'description' => $this->guestCartItemDescription($item, $product),
            'price' => $unitPrice,
            'quantity' => $quantity,
            'size' => $item['size'] ?? null,
            'flavor' => $item['flavor'] ?? null,
            'image' => $this->cartItemImageUrlFromPayload($item, $product),
            'tag' => $isCustom ? 'Custom Order' : ($product?->is_active ? 'Available' : 'Unavailable'),
            'average_rating' => $product?->average_rating,
            'review_count' => $product?->review_count,
            'minimumQuantity' => (int) ($selectedOption['minimum_quantity'] ?? 1),
            'designDescription' => $item['design_description'] ?? null,
            'dedicationMessage' => $item['dedication_message'] ?? null,
            'line_total' => round($unitPrice * $quantity, 2),
        ];
    }

    private function cartItemUnitPrice(CartItem $item): float
    {
        if ($item->item_type === 'custom') {
            return (float) ($item->unit_price ?? 0);
        }

        return (float) ($item->unit_price ?? $item->product?->price ?? 0);
    }

    private function cartItemName(CartItem $item): string
    {
        if ($item->item_type === 'custom') {
            return $item->product_name ?: 'Custom Celebration Order';
        }

        return $item->product?->product_name ?? $item->product_name ?? 'Bakerdan Product';
    }

    private function cartItemDescription(CartItem $item): string
    {
        if ($item->item_type === 'custom') {
            return $item->description ?: $this->customDescription($item->size, $item->flavor);
        }

        return $item->product?->description ?? $item->description ?? '';
    }

    /**
     * @param array<string, mixed> $item
     */
    private function guestCartItemName(array $item, ?Product $product): string
    {
        if (($item['item_type'] ?? 'catalog') === 'custom') {
            return (string) ($item['product_name'] ?? 'Custom Celebration Order');
        }

        return $product?->product_name ?? (string) ($item['product_name'] ?? 'Bakerdan Product');
    }

    /**
     * @param array<string, mixed> $item
     */
    private function guestCartItemDescription(array $item, ?Product $product): string
    {
        if (($item['item_type'] ?? 'catalog') === 'custom') {
            return (string) ($item['description'] ?? $this->customDescription($item['size'] ?? null, $item['flavor'] ?? null));
        }

        return $product?->description ?? (string) ($item['description'] ?? '');
    }

    private function customUnitPrice(string $size, ?float $basePrice = null): float
    {
        if ($basePrice !== null && $basePrice >= 0) {
            return round($basePrice, 2);
        }

        return match ($size) {
            'Small' => 500.00,
            'Medium' => 750.00,
            'Large' => 1000.00,
            default => 500.00,
        };
    }

    private function customDescription(?string $size, ?string $flavor, ?string $productName = null, ?string $guideSection = null): string
    {
        $productLabel = $productName ?: 'Custom Celebration Order';
        $parts = array_filter([
            $guideSection ? 'Guide: ' . $guideSection : null,
            $size ? 'Size: ' . $size : null,
            $flavor ? 'Flavor: ' . $flavor : null,
        ]);

        if ($parts === []) {
            return $productLabel . ' customized to the customer brief.';
        }

        return $productLabel . ' | ' . implode(' | ', $parts);
    }

    private function shippingFeeFor(string $fulfillmentMethod, bool $hasItems): float
    {
        if (! $hasItems) {
            return 0;
        }

        return $fulfillmentMethod === 'pickup' ? 0 : self::SHIPPING_FEE;
    }

    private function formatShippingAddress(string $fulfillmentMethod, ?string $address, ?string $pinLink): ?string
    {
        if ($fulfillmentMethod === 'pickup') {
            return sprintf(
                'Pickup at %s | %s | Pin link: %s',
                self::PICKUP_SHOP_NAME,
                self::PICKUP_SHOP_ADDRESS,
                self::PICKUP_SHOP_PIN_LINK,
            );
        }

        $address = filled($address) ? trim((string) $address) : null;
        $pinLink = filled($pinLink) ? trim((string) $pinLink) : null;

        if ($address && $pinLink) {
            return $address . ' | Pin link: ' . $pinLink;
        }

        if ($address) {
            return $address;
        }

        if ($pinLink) {
            return 'Pin link: ' . $pinLink;
        }

        return null;
    }

    private function resolveImageUrl(?string $imagePath): ?string
    {
        if (!$imagePath) {
            return null;
        }

        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://')) {
            return $imagePath;
        }

        if (str_starts_with($imagePath, '/')) {
            $awsRoot = rtrim(config('app.aws_url', ''), '/');
            if ($awsRoot !== '') {
                return $awsRoot . '/' . ltrim($imagePath, '/');
            }
            return $imagePath;
        }

        $publicRelativePath = ltrim($imagePath, '/');
        if (file_exists(public_path($publicRelativePath))) {
            return asset($publicRelativePath);
        }

        return Storage::disk('s3')->url($imagePath);
    }

    private function selectedCartItemOption(CartItem $item): ?array
    {
        if (! $item->product) {
            return null;
        }

        foreach ($this->productOptions->optionsFor($item->product) as $option) {
            $sizeMatches = blank($item->size) || ($option['size'] ?? null) === $item->size;
            $flavorMatches = blank($item->flavor) || ($option['flavor'] ?? null) === $item->flavor;

            if ($sizeMatches && $flavorMatches) {
                return $option;
            }
        }

        return null;
    }

    /**
     * @param array<string, mixed> $item
     */
    private function selectedGuestCartItemOption(array $item, Product $product): ?array
    {
        foreach ($this->productOptions->optionsFor($product) as $option) {
            $sizeMatches = blank($item['size'] ?? null) || ($option['size'] ?? null) === ($item['size'] ?? null);
            $flavorMatches = blank($item['flavor'] ?? null) || ($option['flavor'] ?? null) === ($item['flavor'] ?? null);

            if ($sizeMatches && $flavorMatches) {
                return $option;
            }
        }

        return null;
    }

    private function cartItemImageUrl(CartItem $item): string
    {
        $resolved = $this->resolveImageUrl(
            $item->item_type === 'custom' ? $item->image_url : ($item->product?->image_url ?? $item->image_url)
        );

        return $resolved ?: $this->defaultCustomOrderImageUrl();
    }

    /**
     * @param array<string, mixed> $item
     */
    private function cartItemImageUrlFromPayload(array $item, ?Product $product): string
    {
        $resolved = $this->resolveImageUrl(
            ($item['item_type'] ?? 'catalog') === 'custom'
                ? ($item['image_url'] ?? null)
                : ($product?->image_url ?? ($item['image_url'] ?? null))
        );

        return $resolved ?: $this->defaultCustomOrderImageUrl();
    }

    private function hasCustomDesignInput(Request $request): bool
    {
        return $request->hasFile('image')
            || filled($request->input('image_url'))
            || filled($request->input('design_description'));
    }

    private function serializeOrder(Order $order): array
    {
        return [
            'contains_custom' => $order->items->contains(fn (OrderItem $item) => $item->item_type === 'custom'),
            'checkout_url' => $order->payment_checkout_url,
            'custom_item_count' => $order->items->where('item_type', 'custom')->count(),
            'id' => $order->id,
            'item_count' => $order->items->sum('quantity'),
            'payment_method' => $order->payment_method,
            'payment_provider' => $order->payment_provider,
            'payment_status' => $order->payment_status,
            'reference' => $order->payment_reference,
            'status' => $order->status,
            'total_amount' => (float) $order->total_amount,
        ];
    }

    private function defaultCustomOrderImageUrl(): string
    {
        return asset('images/bakerdan/Cake_Celebration.png');
    }

    private function notifyCustomerOrderSentToAdmin(Order $order): void
    {
        if (! $order->user_id) {
            return;
        }

        $itemCount = (int) $order->items->sum('quantity');
        $containsCustom = $order->items->contains(fn (OrderItem $item) => $item->item_type === 'custom');
        $paymentMethod = match (strtolower((string) $order->payment_method)) {
            'maya', 'paymaya' => 'Maya',
            default => 'GCash',
        };

        CustomerNotification::query()->create([
            'user_id' => $order->user_id,
            'type' => 'order',
            'title' => 'Order sent to admin',
            'message' => $containsCustom
                ? "Order #{$order->id} with {$itemCount} item(s) was sent to BakerDan admin for review and custom order processing."
                : "Order #{$order->id} with {$itemCount} item(s) was sent to BakerDan admin. Please complete your {$paymentMethod} checkout to confirm it.",
            'payload' => [
                'action' => 'open_order',
                'order_id' => $order->id,
                'payment_method' => $order->payment_method,
                'payment_status' => $order->payment_status,
                'source' => 'customer_checkout',
            ],
            'image_url' => null,
        ]);
    }

    public function getActivePromos(): JsonResponse
    {
        $now = now();
        $promos = Promo::where('is_active', true)
            ->where(function ($query) use ($now) {
                $query->whereNull('starts_at')
                      ->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($query) use ($now) {
                $query->whereNull('expires_at')
                      ->orWhere('expires_at', '>=', $now);
            })
            ->get();

        $promos = $promos->filter(function ($promo) {
            if ($promo->usage_limit !== null && $promo->usage_count >= $promo->usage_limit) {
                return false;
            }
            return true;
        })->values();

        return response()->json([
            'success' => true,
            'promos' => $promos,
        ]);
    }

    public function validatePromoCode(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string',
            'item_ids' => 'required|array',
            'item_ids.*' => 'integer',
        ]);

        $code = strtoupper(trim($request->input('code')));
        $itemIds = $request->input('item_ids');

        $promo = Promo::where('code', $code)->first();

        if (!$promo) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid promo code.',
            ], 422);
        }

        $result = $this->calculatePromoDiscount($promo, $itemIds, (int) Auth::id());

        if (!$result['valid']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'],
            ], 422);
        }

        return response()->json([
            'success' => true,
            'promo_id' => $promo->id,
            'code' => $promo->code,
            'discount_amount' => $result['discount_amount'],
            'applicable_subtotal' => $result['applicable_subtotal'],
            'message' => 'Promo code applied successfully!',
        ]);
    }

    private function calculatePromoDiscount(Promo $promo, array $itemIds, int $userId): array
    {
        $now = now();

        if (!$promo->is_active) {
            return ['valid' => false, 'message' => 'This promo code is no longer active.'];
        }

        if ($promo->starts_at && $now->lt($promo->starts_at)) {
            return ['valid' => false, 'message' => 'This promo code is not available yet.'];
        }
        if ($promo->expires_at && $now->gt($promo->expires_at)) {
            return ['valid' => false, 'message' => 'This promo code has expired.'];
        }

        if ($promo->usage_limit !== null && $promo->usage_count >= $promo->usage_limit) {
            return ['valid' => false, 'message' => 'This promo code has reached its global limit.'];
        }

        if ($promo->limit_per_user !== null) {
            $userUsage = Order::where('user_id', $userId)
                ->where('promo_id', $promo->id)
                ->where('payment_status', 'paid')
                ->count();
            if ($userUsage >= $promo->limit_per_user) {
                return ['valid' => false, 'message' => 'You have already reached the maximum usage limit for this promo code.'];
            }
        }

        $cart = $this->userCart();
        if (!$cart) {
            return ['valid' => false, 'message' => 'Cart not found.'];
        }

        $items = $cart->items()
            ->with('product')
            ->whereIn('id', $itemIds)
            ->get();

        if ($items->isEmpty()) {
            return ['valid' => false, 'message' => 'No items found for checkout.'];
        }

        $applicableProducts = $promo->applicable_products;
        $applicableItems = $items;

        if (is_array($applicableProducts) && count($applicableProducts) > 0) {
            $applicableItems = $items->filter(function (CartItem $item) use ($applicableProducts) {
                return in_array((int)$item->product_id, $applicableProducts);
            });

            if ($applicableItems->isEmpty()) {
                return ['valid' => false, 'message' => 'This promo code is not applicable to any of the selected products.'];
            }
        }

        $applicableSubtotal = $applicableItems->sum(fn (CartItem $item) => $this->cartItemUnitPrice($item) * $item->quantity);

        if ($promo->min_purchase !== null && $applicableSubtotal < $promo->min_purchase) {
            return [
                'valid' => false, 
                'message' => 'Minimum purchase of PHP ' . number_format($promo->min_purchase, 2) . ' is required for applicable products.'
            ];
        }

        $discountAmount = 0.00;
        if ($promo->discount_type === 'percentage') {
            $discountAmount = ($applicableSubtotal * $promo->discount_value) / 100;
            if ($promo->max_discount !== null && $discountAmount > $promo->max_discount) {
                $discountAmount = $promo->max_discount;
            }
        } else {
            $discountAmount = $promo->discount_value;
            if ($discountAmount > $applicableSubtotal) {
                $discountAmount = $applicableSubtotal;
            }
        }

        return [
            'valid' => true,
            'discount_amount' => round($discountAmount, 2),
            'applicable_subtotal' => round($applicableSubtotal, 2),
        ];
    }
}
