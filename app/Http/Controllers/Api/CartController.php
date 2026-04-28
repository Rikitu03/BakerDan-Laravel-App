<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Services\CustomOrderImageService;
use App\Services\PayMongoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Throwable;

class CartController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $this->cartSummary($this->userCart()),
        ]);
    }

    public function add(Request $request, int $productId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:99',
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

        $cart = $this->userCart(true);
        $validated = $validator->validated();
        $size = $validated['size'] ?? null;
        $flavor = $validated['flavor'] ?? null;

        $cartItem = DB::transaction(function () use ($cart, $product, $validated, $size, $flavor): CartItem {
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
                'unit_price' => (float) $product->price,
                'product_name' => $product->product_name,
                'description' => $product->description,
                'image_url' => $product->image_url,
                'size' => $size,
                'flavor' => $flavor,
            ])->load('product');
        });

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully.',
            'data' => [
                'item' => $this->serializeCartItem($cartItem),
                'cart' => $this->cartSummary($cart->fresh()),
            ],
        ]);
    }

    public function addCustom(Request $request, CustomOrderImageService $customOrderImageService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'size' => 'required|string|in:Small,Medium,Large',
            'quantity' => 'required|integer|min:1|max:99',
            'flavor' => 'required|string|max:100',
            'design_description' => 'nullable|string|max:300',
            'dedication_message' => 'nullable|string|max:300',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'image_url' => 'nullable|string|max:2048',
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
        $cart = $this->userCart(true);
        $imagePath = $validated['image_url'] ?? null;

        if ($request->hasFile('image')) {
            $imagePath = $customOrderImageService->storeReferenceImage($request->file('image'));
        }

        $cartItem = $cart->items()->create([
            'product_id' => null,
            'item_type' => 'custom',
            'quantity' => (int) $validated['quantity'],
            'unit_price' => $this->customUnitPrice($validated['size']),
            'product_name' => 'Custom Celebration Order',
            'description' => $this->customDescription($validated['size'], $validated['flavor']),
            'image_url' => $imagePath,
            'design_description' => $validated['design_description'] ?? null,
            'dedication_message' => $validated['dedication_message'] ?? null,
            'size' => $validated['size'],
            'flavor' => $validated['flavor'],
        ]);

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

    public function remove(int $itemId): JsonResponse
    {
        $cartItem = $this->ownedCartItem($itemId);
        $cart = $cartItem->cart;
        $cartItem->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart.',
            'data' => [
                'cart' => $this->cartSummary($cart->fresh()),
            ],
        ]);
    }

    public function clear(): JsonResponse
    {
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

    public function checkout(Request $request, PayMongoService $payMongo): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'item_ids' => 'required|array|min:1',
            'item_ids.*' => 'integer',
            'payment_method' => 'required|string|in:gcash,maya',
            'shipping_address' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
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

        $order = DB::transaction(function () use ($validated, $items): Order {
            $subtotal = $items->sum(fn (CartItem $item) => $this->cartItemUnitPrice($item) * $item->quantity);

            $order = Order::query()->create([
                'user_id' => Auth::id(),
                'total_amount' => $subtotal,
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $validated['payment_method'],
                'payment_provider' => 'paymongo',
                'shipping_address' => $validated['shipping_address'] ?? null,
            ]);

            foreach ($items as $item) {
                $order->items()->create([
                    'product_id' => $item->product_id,
                    'item_type' => $item->item_type,
                    'quantity' => $item->quantity,
                    'price' => $this->cartItemUnitPrice($item),
                    'product_name' => $this->cartItemName($item),
                    'description' => $this->cartItemDescription($item),
                    'image_url' => $item->item_type === 'custom' ? $item->image_url : $item->product?->image_url,
                    'design_description' => $item->design_description,
                    'dedication_message' => $item->dedication_message,
                    'size' => $item->size,
                    'flavor' => $item->flavor,
                ]);
            }

            return $order->load(['items', 'user.detail']);
        });

        try {
            $checkoutSession = $payMongo->createCheckoutSession($order);
        } catch (Throwable $exception) {
            report($exception);
            $order->delete();

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage() ?: 'Unable to start the PayMongo checkout right now.',
            ], 422);
        }

        DB::transaction(function () use ($checkoutSession, $items, $order): void {
            $order->update([
                'payment_reference' => data_get($checkoutSession, 'attributes.reference_number'),
                'payment_session_id' => $checkoutSession['id'] ?? null,
                'payment_checkout_url' => data_get($checkoutSession, 'attributes.checkout_url'),
                'payment_metadata' => array_filter([
                    'checkout_session_status' => data_get($checkoutSession, 'attributes.status'),
                    'payment_intent_id' => data_get($checkoutSession, 'attributes.payment_intent.id'),
                    'payment_intent_status' => data_get($checkoutSession, 'attributes.payment_intent.attributes.status'),
                ], fn ($value) => $value !== null && $value !== ''),
            ]);

            $items->each->delete();
        });

        $order = $order->fresh('items');

        return response()->json([
            'success' => true,
            'message' => 'Checkout session created successfully.',
            'data' => [
                'order' => $this->serializeOrder($order),
                'cart' => $this->cartSummary($cart->fresh()),
            ],
        ]);
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
            'item_count' => $serializedItems->sum('quantity'),
        ];
    }

    private function serializeCartItem(CartItem $item): array
    {
        $unitPrice = $this->cartItemUnitPrice($item);

        return [
            'id' => $item->id,
            'product_id' => $item->product_id,
            'source' => $item->item_type === 'custom' ? 'custom' : 'catalog',
            'name' => $this->cartItemName($item),
            'description' => $this->cartItemDescription($item),
            'price' => $unitPrice,
            'quantity' => $item->quantity,
            'size' => $item->size,
            'flavor' => $item->flavor,
            'image' => $this->cartItemImageUrl($item),
            'tag' => $item->item_type === 'custom' ? 'Custom Order' : ($item->product?->is_active ? 'Available' : 'Unavailable'),
            'designDescription' => $item->design_description,
            'dedicationMessage' => $item->dedication_message,
            'line_total' => round($unitPrice * $item->quantity, 2),
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

    private function customUnitPrice(string $size): float
    {
        return match ($size) {
            'Small' => 500.00,
            'Medium' => 750.00,
            'Large' => 1000.00,
            default => 500.00,
        };
    }

    private function customDescription(?string $size, ?string $flavor): string
    {
        $normalizedSize = strtolower($size ?: 'medium');
        $normalizedFlavor = strtolower($flavor ?: 'custom');

        return "A {$normalizedFlavor} custom bake with {$normalizedSize} sizing and personalized finishing touches.";
    }

    private function resolveImageUrl(?string $imagePath): ?string
    {
        if (!$imagePath) {
            return null;
        }

        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://') || str_starts_with($imagePath, '/')) {
            return $imagePath;
        }

        if (Storage::disk('public')->exists($imagePath)) {
            return Storage::disk('public')->url($imagePath);
        }

        return asset('storage/' . ltrim($imagePath, '/'));
    }

    private function cartItemImageUrl(CartItem $item): string
    {
        $resolved = $this->resolveImageUrl(
            $item->item_type === 'custom' ? $item->image_url : ($item->product?->image_url ?? $item->image_url)
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
}
