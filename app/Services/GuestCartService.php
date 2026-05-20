<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GuestCartService
{
    private const ITEMS_KEY = 'guest_cart.items';
    private const NEXT_ID_KEY = 'guest_cart.next_id';
    private const GUEST_SESSION_KEY = 'guest_customer';
    private const INTENDED_URL_KEY = 'guest_customer.intended_url';

    /**
     * @return array<int, array<string, mixed>>
     */
    public function items(Request $request): array
    {
        $items = $request->session()->get(self::ITEMS_KEY, []);

        return is_array($items) ? array_values($items) : [];
    }

    public function hasItems(Request $request): bool
    {
        return $this->items($request) !== [];
    }

    public function markGuest(Request $request): void
    {
        $request->session()->put(self::GUEST_SESSION_KEY, true);
    }

    public function isMarkedGuest(Request $request): bool
    {
        return (bool) $request->session()->get(self::GUEST_SESSION_KEY, false);
    }

    public function storeIntendedUrl(Request $request, ?string $url): ?string
    {
        $safeUrl = $this->safeCustomerUrl($url);

        if ($safeUrl) {
            $request->session()->put(self::INTENDED_URL_KEY, $safeUrl);
        }

        return $safeUrl;
    }

    public function intendedUrl(Request $request): ?string
    {
        $url = $request->input('redirect')
            ?: $request->query('redirect')
            ?: $request->session()->get(self::INTENDED_URL_KEY);

        return $this->safeCustomerUrl(is_string($url) ? $url : null);
    }

    public function pullIntendedUrl(Request $request): ?string
    {
        $url = $this->intendedUrl($request);
        $request->session()->forget(self::INTENDED_URL_KEY);

        return $url;
    }

    /**
     * @param array<string, mixed> $validated
     * @param array<string, mixed>|null $option
     * @return array<string, mixed>
     */
    public function addCatalogItem(Request $request, Product $product, array $validated, ?array $option = null): array
    {
        $items = $this->items($request);
        $size = $option['size'] ?? ($validated['size'] ?? null);
        $flavor = $option['flavor'] ?? ($validated['flavor'] ?? null);
        $quantity = (int) $validated['quantity'];

        foreach ($items as $index => $item) {
            if (($item['item_type'] ?? 'catalog') !== 'catalog') {
                continue;
            }

            if ((int) ($item['product_id'] ?? 0) !== (int) $product->id) {
                continue;
            }

            if (($item['size'] ?? null) !== $size || ($item['flavor'] ?? null) !== $flavor) {
                continue;
            }

            $items[$index]['quantity'] = (int) ($item['quantity'] ?? 0) + $quantity;
            $this->putItems($request, $items);

            return $items[$index];
        }

        $item = [
            'id' => $this->nextItemId($request),
            'product_id' => $product->id,
            'item_type' => 'catalog',
            'quantity' => $quantity,
            'unit_price' => (float) ($option['price'] ?? $product->price),
            'product_name' => $product->product_name,
            'description' => $product->description,
            'image_url' => $product->image_url,
            'size' => $size,
            'flavor' => $flavor,
            'created_at' => now()->toIso8601String(),
        ];

        $items[] = $item;
        $this->putItems($request, $items);

        return $item;
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function addCustomItem(Request $request, array $payload): array
    {
        $items = $this->items($request);
        $item = array_merge([
            'id' => $this->nextItemId($request),
            'product_id' => null,
            'item_type' => 'custom',
            'created_at' => now()->toIso8601String(),
        ], $payload);

        $items[] = $item;
        $this->putItems($request, $items);

        return $item;
    }

    public function updateQuantity(Request $request, int $itemId, int $quantity): ?array
    {
        $items = $this->items($request);

        foreach ($items as $index => $item) {
            if ((int) ($item['id'] ?? 0) !== $itemId) {
                continue;
            }

            $items[$index]['quantity'] = $quantity;
            $this->putItems($request, $items);

            return $items[$index];
        }

        return null;
    }

    public function removeItem(Request $request, int $itemId): bool
    {
        $items = $this->items($request);
        $filtered = array_values(array_filter(
            $items,
            fn (array $item): bool => (int) ($item['id'] ?? 0) !== $itemId,
        ));

        $this->putItems($request, $filtered);

        return count($filtered) !== count($items);
    }

    public function clear(Request $request): void
    {
        $request->session()->forget([self::ITEMS_KEY, self::NEXT_ID_KEY]);
    }

    /**
     * @return array{
     *     cart: Cart,
     *     item_id_map: array<int, int>,
     *     all_cart_item_ids: array<int, int>
     * }
     */
    public function mergeIntoUserCart(Request $request, int $userId): array
    {
        $guestItems = $this->items($request);
        $itemIdMap = [];

        $cart = DB::transaction(function () use ($guestItems, $userId, &$itemIdMap): Cart {
            $cart = Cart::query()->firstOrCreate([
                'user_id' => $userId,
            ]);

            foreach ($guestItems as $guestItem) {
                $createdOrUpdated = $this->mergeGuestItem($cart, $guestItem);
                $guestItemId = (int) ($guestItem['id'] ?? 0);

                if ($guestItemId > 0) {
                    $itemIdMap[$guestItemId] = (int) $createdOrUpdated->id;
                }
            }

            return $cart->fresh(['items']);
        });

        $this->clear($request);
        $request->session()->forget(self::GUEST_SESSION_KEY);

        return [
            'cart' => $cart,
            'item_id_map' => $itemIdMap,
            'all_cart_item_ids' => $cart->items()->latest('id')->pluck('id')->map(fn ($id): int => (int) $id)->values()->all(),
        ];
    }

    private function mergeGuestItem(Cart $cart, array $guestItem): CartItem
    {
        if (($guestItem['item_type'] ?? 'catalog') === 'catalog') {
            $existingItem = $cart->items()
                ->where('item_type', 'catalog')
                ->where('product_id', $guestItem['product_id'] ?? null)
                ->when($guestItem['size'] ?? null, fn ($query, $size) => $query->where('size', $size), fn ($query) => $query->whereNull('size'))
                ->when($guestItem['flavor'] ?? null, fn ($query, $flavor) => $query->where('flavor', $flavor), fn ($query) => $query->whereNull('flavor'))
                ->first();

            if ($existingItem) {
                $existingItem->increment('quantity', (int) ($guestItem['quantity'] ?? 1));

                return $existingItem->fresh(['product']);
            }
        }

        return $cart->items()->create([
            'product_id' => $guestItem['product_id'] ?? null,
            'item_type' => $guestItem['item_type'] ?? 'catalog',
            'quantity' => (int) ($guestItem['quantity'] ?? 1),
            'unit_price' => (float) ($guestItem['unit_price'] ?? 0),
            'product_name' => $guestItem['product_name'] ?? null,
            'description' => $guestItem['description'] ?? null,
            'image_url' => $guestItem['image_url'] ?? null,
            'design_description' => $guestItem['design_description'] ?? null,
            'dedication_message' => $guestItem['dedication_message'] ?? null,
            'size' => $guestItem['size'] ?? null,
            'flavor' => $guestItem['flavor'] ?? null,
        ])->load('product');
    }

    /**
     * @param array<int, array<string, mixed>> $items
     */
    private function putItems(Request $request, array $items): void
    {
        $request->session()->put(self::ITEMS_KEY, array_values($items));
    }

    private function nextItemId(Request $request): int
    {
        $nextId = max(1, (int) $request->session()->get(self::NEXT_ID_KEY, 1));
        $request->session()->put(self::NEXT_ID_KEY, $nextId + 1);

        return $nextId;
    }

    private function safeCustomerUrl(?string $url): ?string
    {
        if (! $url) {
            return null;
        }

        $parts = parse_url($url);
        $path = $parts['path'] ?? null;

        if (! is_string($path) || $path === '') {
            return null;
        }

        if (! str_starts_with($path, '/customer') && $path !== '/cart') {
            return null;
        }

        $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';

        return $path . $query;
    }
}
