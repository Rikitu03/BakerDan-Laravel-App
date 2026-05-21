<?php

namespace App\Services;

use App\Models\CustomerNotification;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PendingOrderService
{
    private const STORE = 'file';
    private const ORDER_PREFIX = 'pending-order:item:';
    private const USER_INDEX_PREFIX = 'pending-order:user:';
    private const SESSION_PREFIX = 'pending-order:session:';
    private const RESOLVED_PREFIX = 'pending-order:resolved:';

    public function create(array $attributes): array
    {
        $now = now();
        $expiresAt = ($attributes['expires_at'] ?? $now->copy()->addHours(2));
        $items = collect($attributes['items'] ?? [])->values()->map(function (array $item): array {
            return [
                'product_id' => $item['product_id'] ?? null,
                'item_type' => $item['item_type'] ?? 'catalog',
                'quantity' => (int) ($item['quantity'] ?? 1),
                'unit_price' => round((float) ($item['unit_price'] ?? 0), 2),
                'product_name' => $item['product_name'] ?? 'Bakerdan item',
                'description' => $item['description'] ?? '',
                'image_url' => $item['image_url'] ?? null,
                'design_description' => $item['design_description'] ?? null,
                'dedication_message' => $item['dedication_message'] ?? null,
                'size' => $item['size'] ?? null,
                'flavor' => $item['flavor'] ?? null,
            ];
        })->all();

        $pendingOrder = [
            'id' => $attributes['id'] ?? ('pending-' . Str::lower((string) Str::uuid())),
            'user_id' => (int) ($attributes['user_id'] ?? 0),
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => $attributes['payment_method'] ?? 'gcash',
            'payment_provider' => $attributes['payment_provider'] ?? 'paymongo',
            'payment_reference' => $attributes['payment_reference'] ?? $this->generateReference(),
            'payment_session_id' => $attributes['payment_session_id'] ?? null,
            'payment_checkout_url' => $attributes['payment_checkout_url'] ?? null,
            'payment_metadata' => is_array($attributes['payment_metadata'] ?? null) ? $attributes['payment_metadata'] : [],
            'shipping_address' => $attributes['shipping_address'] ?? null,
            'total_amount' => round((float) ($attributes['total_amount'] ?? 0), 2),
            'promo_id' => isset($attributes['promo_id']) ? (int) $attributes['promo_id'] : null,
            'discount_amount' => round((float) ($attributes['discount_amount'] ?? 0), 2),
            'created_at' => $this->normalizeCarbon($attributes['created_at'] ?? $now)->toIso8601String(),
            'expires_at' => $this->normalizeCarbon($expiresAt)->toIso8601String(),
            'items' => $items,
        ];

        $this->put($pendingOrder);

        return $pendingOrder;
    }

    public function put(array $pendingOrder): array
    {
        $pendingOrder['payment_metadata'] = is_array($pendingOrder['payment_metadata'] ?? null) ? $pendingOrder['payment_metadata'] : [];
        $pendingOrder['items'] = array_values($pendingOrder['items'] ?? []);

        $expiresAt = $this->expiresAt($pendingOrder);

        if (now()->greaterThanOrEqualTo($expiresAt)) {
            $this->forget((string) $pendingOrder['id']);
            return $pendingOrder;
        }

        $this->cache()->put($this->orderKey($pendingOrder['id']), $pendingOrder, $expiresAt);
        $this->touchUserIndex((int) $pendingOrder['user_id'], (string) $pendingOrder['id']);

        if (filled($pendingOrder['payment_session_id'] ?? null)) {
            $this->cache()->put($this->sessionKey((string) $pendingOrder['payment_session_id']), (string) $pendingOrder['id'], $expiresAt);
        }

        return $pendingOrder;
    }

    public function allForUser(int $userId): array
    {
        $pendingIds = collect($this->cache()->get($this->userIndexKey($userId), []));
        $activeIds = [];
        $orders = [];

        foreach ($pendingIds as $pendingId) {
            $pendingOrder = $this->find((string) $pendingId);

            if (! $pendingOrder) {
                continue;
            }

            if ($this->isExpired($pendingOrder)) {
                $this->forget((string) $pendingOrder['id']);
                continue;
            }

            $activeIds[] = (string) $pendingOrder['id'];
            $orders[] = $pendingOrder;
        }

        $this->cache()->put($this->userIndexKey($userId), array_values(array_unique($activeIds)), now()->addDay());

        usort($orders, fn (array $left, array $right): int => strcmp($right['created_at'] ?? '', $left['created_at'] ?? ''));

        return $orders;
    }

    public function find(string $pendingOrderId): ?array
    {
        $pendingOrder = $this->cache()->get($this->orderKey($pendingOrderId));

        return is_array($pendingOrder) ? $pendingOrder : null;
    }

    public function findForUser(int $userId, string $pendingOrderId): ?array
    {
        $pendingOrder = $this->find($pendingOrderId);

        if (! $pendingOrder || (int) ($pendingOrder['user_id'] ?? 0) !== $userId) {
            return null;
        }

        if ($this->isExpired($pendingOrder)) {
            $this->forget($pendingOrderId);
            return null;
        }

        return $pendingOrder;
    }

    public function findBySessionId(string $paymentSessionId): ?array
    {
        $pendingOrderId = $this->cache()->get($this->sessionKey($paymentSessionId));

        if (! is_string($pendingOrderId) || $pendingOrderId === '') {
            return null;
        }

        return $this->find($pendingOrderId);
    }

    public function resolveMaterializedOrderId(string $pendingOrderId): ?int
    {
        $resolvedOrderId = $this->cache()->get($this->resolvedKey($pendingOrderId));

        return is_numeric($resolvedOrderId) ? (int) $resolvedOrderId : null;
    }

    public function rememberMaterializedOrder(string $pendingOrderId, int $orderId): void
    {
        $this->cache()->put($this->resolvedKey($pendingOrderId), $orderId, now()->addDay());
    }

    public function cancelForUser(int $userId, string $pendingOrderId): bool
    {
        $pendingOrder = $this->findForUser($userId, $pendingOrderId);

        if (! $pendingOrder || ! $this->canCancel($pendingOrder)) {
            return false;
        }

        $this->forget($pendingOrderId);

        return true;
    }

    public function canCancel(array $pendingOrder): bool
    {
        return ! $this->isExpired($pendingOrder)
            && ($pendingOrder['status'] ?? 'pending') === 'pending'
            && ($pendingOrder['payment_status'] ?? 'pending') !== 'paid';
    }

    public function canContinuePayment(array $pendingOrder): bool
    {
        return ! $this->isExpired($pendingOrder)
            && filled($pendingOrder['payment_checkout_url'] ?? null)
            && ($pendingOrder['payment_status'] ?? 'pending') !== 'paid';
    }

    public function isExpired(array $pendingOrder): bool
    {
        return now()->greaterThanOrEqualTo($this->expiresAt($pendingOrder));
    }

    public function syncCheckoutSession(array $pendingOrder, array $checkoutSession, string $paymentStatus = 'pending'): array
    {
        $metadata = is_array($pendingOrder['payment_metadata'] ?? null) ? $pendingOrder['payment_metadata'] : [];
        $pendingOrder['payment_status'] = $paymentStatus === 'paid' ? 'paid' : 'pending';
        $pendingOrder['payment_reference'] = data_get($checkoutSession, 'attributes.reference_number', $pendingOrder['payment_reference'] ?? $this->generateReference());
        $pendingOrder['payment_session_id'] = $checkoutSession['id'] ?? ($pendingOrder['payment_session_id'] ?? null);
        $pendingOrder['payment_checkout_url'] = data_get($checkoutSession, 'attributes.checkout_url', $pendingOrder['payment_checkout_url'] ?? null);
        $pendingOrder['payment_metadata'] = array_merge($metadata, array_filter([
            'checkout_session_status' => data_get($checkoutSession, 'attributes.status'),
            'last_synced_at' => now()->toIso8601String(),
            'payment_intent_id' => data_get($checkoutSession, 'attributes.payment_intent.id'),
            'payment_intent_status' => data_get($checkoutSession, 'attributes.payment_intent.attributes.status'),
            'payment_method_used' => data_get($checkoutSession, 'attributes.payment_method_used'),
        ], fn ($value) => $value !== null && $value !== ''));

        return $this->put($pendingOrder);
    }

    public function materializePaidOrder(array $pendingOrder, array $checkoutSession, PayMongoService $payMongo): Order
    {
        $resolvedOrderId = $this->resolveMaterializedOrderId((string) $pendingOrder['id']);

        if ($resolvedOrderId) {
            return Order::query()
                ->with(['items.product'])
                ->whereKey($resolvedOrderId)
                ->firstOrFail();
        }

        $paymentSessionId = $checkoutSession['id'] ?? ($pendingOrder['payment_session_id'] ?? null);

        if (filled($paymentSessionId)) {
            $existingOrder = Order::query()
                ->with(['items.product'])
                ->where('payment_session_id', $paymentSessionId)
                ->first();

            if ($existingOrder) {
                $existingOrder = $payMongo->syncOrderPayment($existingOrder, $checkoutSession);
                $this->rememberMaterializedOrder((string) $pendingOrder['id'], (int) $existingOrder->id);
                $this->forget((string) $pendingOrder['id']);

                return $existingOrder;
            }
        }

        $order = DB::transaction(function () use ($pendingOrder): Order {
            $order = Order::query()->create([
                'user_id' => (int) $pendingOrder['user_id'],
                'total_amount' => round((float) ($pendingOrder['total_amount'] ?? 0), 2),
                'status' => 'pending',
                'payment_status' => 'pending',
                'payment_method' => $pendingOrder['payment_method'] ?? 'gcash',
                'payment_provider' => $pendingOrder['payment_provider'] ?? 'paymongo',
                'payment_reference' => $pendingOrder['payment_reference'] ?? null,
                'payment_session_id' => $pendingOrder['payment_session_id'] ?? null,
                'payment_checkout_url' => $pendingOrder['payment_checkout_url'] ?? null,
                'payment_expires_at' => $this->expiresAt($pendingOrder),
                'payment_metadata' => is_array($pendingOrder['payment_metadata'] ?? null) ? $pendingOrder['payment_metadata'] : [],
                'shipping_address' => $pendingOrder['shipping_address'] ?? null,
                'promo_id' => $pendingOrder['promo_id'] ?? null,
                'discount_amount' => round((float) ($pendingOrder['discount_amount'] ?? 0), 2),
            ]);

            foreach (($pendingOrder['items'] ?? []) as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'] ?? null,
                    'item_type' => $item['item_type'] ?? 'catalog',
                    'quantity' => (int) ($item['quantity'] ?? 1),
                    'price' => round((float) ($item['unit_price'] ?? 0), 2),
                    'product_name' => $item['product_name'] ?? 'Bakerdan item',
                    'description' => $item['description'] ?? '',
                    'image_url' => $item['image_url'] ?? null,
                    'design_description' => $item['design_description'] ?? null,
                    'dedication_message' => $item['dedication_message'] ?? null,
                    'size' => $item['size'] ?? null,
                    'flavor' => $item['flavor'] ?? null,
                ]);
            }

            if ($order->promo_id) {
                \App\Models\Promo::whereKey($order->promo_id)->increment('usage_count');
            }

            return $order->load(['items.product']);
        });

        $order = $payMongo->syncOrderPayment($order, $checkoutSession);

        $this->rememberMaterializedOrder((string) $pendingOrder['id'], (int) $order->id);
        $this->forget((string) $pendingOrder['id']);
        
        $customerName = $order->user?->detail?->name ?? 'Customer';
        CustomerNotification::notifyAdmins(
            'order',
            'Payment received',
            'Order #' . $order->id . ' from ' . $customerName . ' is paid and ready for admin handling.',
            [
                'order_id' => $order->id,
                'customer_name' => $customerName,
                'payment_status' => 'paid',
                'contains_custom' => $order->items->contains(fn ($item) => $item->item_type === 'custom'),
                'source' => 'online_payment'
            ]
        );

        $this->notifyCustomerPaymentConfirmed($order);

        return $order->loadMissing(['items.product']);
    }

    public function serializeForCustomer(array $pendingOrder): array
    {
        $createdAt = $this->createdAt($pendingOrder);
        $expiresAt = $this->expiresAt($pendingOrder);
        $items = collect($pendingOrder['items'] ?? [])->values();
        $containsCustom = $items->contains(fn (array $item): bool => ($item['item_type'] ?? 'catalog') === 'custom');

        return [
            'can_cancel' => $this->canCancel($pendingOrder),
            'can_continue_payment' => $this->canContinuePayment($pendingOrder),
            'can_refresh_payment' => filled($pendingOrder['payment_session_id'] ?? null),
            'checkout_url' => $pendingOrder['payment_checkout_url'] ?? null,
            'contains_custom' => $containsCustom,
            'flow_status' => 'pending',
            'flow_status_label' => 'Pending',
            'id' => (string) $pendingOrder['id'],
            'is_current' => ! $this->isExpired($pendingOrder),
            'is_pending_checkout' => true,
            'item_count' => (int) $items->sum(fn (array $item): int => (int) ($item['quantity'] ?? 0)),
            'items' => $items->map(fn (array $item, int $index): array => $this->serializePendingItem($pendingOrder, $item, $index))->all(),
            'payment' => $this->paymentPayloadForCustomer($pendingOrder),
            'payment_method' => $pendingOrder['payment_method'] ?? 'gcash',
            'payment_method_label' => $this->paymentMethodLabel($pendingOrder['payment_method'] ?? null),
            'payment_paid_at' => null,
            'payment_status' => 'pending',
            'payment_status_label' => 'Pending Payment',
            'placed_at' => $createdAt->toIso8601String(),
            'placed_at_label' => $createdAt->format('M d, Y h:i A'),
            'reference' => $pendingOrder['payment_reference'] ?? null,
            'shipping_address' => $pendingOrder['shipping_address'] ?? null,
            'status' => 'pending',
            'status_label' => 'Pending Payment',
            'status_note' => 'Payment is still pending. Complete checkout within 2 hours or this draft will be removed automatically.',
            'total_amount' => round((float) ($pendingOrder['total_amount'] ?? 0), 2),
            'total_amount_label' => 'PHP ' . number_format((float) ($pendingOrder['total_amount'] ?? 0), 2),
            'pending_expires_at' => $expiresAt->toIso8601String(),
            'pending_expires_at_label' => $expiresAt->format('M d, Y h:i A'),
            'pending_countdown_seconds' => max(0, now()->diffInSeconds($expiresAt, false)),
        ];
    }

    public function paymentPayloadForCustomer(array $pendingOrder, ?string $syncError = null): array
    {
        $payload = [
            'checkout_url' => $pendingOrder['payment_checkout_url'] ?? null,
            'gateway_status' => data_get($pendingOrder, 'payment_metadata.payment_intent_status')
                ?: data_get($pendingOrder, 'payment_metadata.checkout_session_status'),
            'provider' => $pendingOrder['payment_provider'] ?? 'paymongo',
            'reference' => $pendingOrder['payment_reference'] ?? null,
            'session_id' => $pendingOrder['payment_session_id'] ?? null,
            'status' => 'pending',
            'sync_error' => $syncError,
        ];

        return array_filter($payload, fn ($value) => $value !== null || $value === 'pending');
    }

    public function forget(string $pendingOrderId): void
    {
        $pendingOrder = $this->find($pendingOrderId);

        if ($pendingOrder) {
            $userId = (int) ($pendingOrder['user_id'] ?? 0);
            $sessionId = $pendingOrder['payment_session_id'] ?? null;

            if ($userId > 0) {
                $remainingIds = collect($this->cache()->get($this->userIndexKey($userId), []))
                    ->filter(fn ($value): bool => (string) $value !== $pendingOrderId)
                    ->values()
                    ->all();

                $this->cache()->put($this->userIndexKey($userId), $remainingIds, now()->addDay());
            }

            if (filled($sessionId)) {
                $this->cache()->forget($this->sessionKey((string) $sessionId));
            }
        }

        $this->cache()->forget($this->orderKey($pendingOrderId));
    }

    private function cache(): CacheRepository
    {
        return Cache::store(self::STORE);
    }

    private function touchUserIndex(int $userId, string $pendingOrderId): void
    {
        if ($userId <= 0) {
            return;
        }

        $pendingIds = collect($this->cache()->get($this->userIndexKey($userId), []))
            ->push($pendingOrderId)
            ->map(fn ($value): string => (string) $value)
            ->unique()
            ->values()
            ->all();

        $this->cache()->put($this->userIndexKey($userId), $pendingIds, now()->addDay());
    }

    private function orderKey(string $pendingOrderId): string
    {
        return self::ORDER_PREFIX . $pendingOrderId;
    }

    private function userIndexKey(int $userId): string
    {
        return self::USER_INDEX_PREFIX . $userId;
    }

    private function sessionKey(string $paymentSessionId): string
    {
        return self::SESSION_PREFIX . $paymentSessionId;
    }

    private function resolvedKey(string $pendingOrderId): string
    {
        return self::RESOLVED_PREFIX . $pendingOrderId;
    }

    private function generateReference(): string
    {
        return 'BD-PND-' . Str::upper(Str::random(8));
    }

    private function createdAt(array $pendingOrder): Carbon
    {
        return $this->normalizeCarbon($pendingOrder['created_at'] ?? now());
    }

    private function expiresAt(array $pendingOrder): Carbon
    {
        return $this->normalizeCarbon($pendingOrder['expires_at'] ?? now()->addHours(2));
    }

    private function normalizeCarbon(mixed $value): Carbon
    {
        if ($value instanceof Carbon) {
            return $value->copy();
        }

        return Carbon::parse($value);
    }

    private function serializePendingItem(array $pendingOrder, array $item, int $index): array
    {
        $unitPrice = round((float) ($item['unit_price'] ?? 0), 2);
        $quantity = (int) ($item['quantity'] ?? 0);
        $detailParts = array_filter([
            filled($item['size'] ?? null) ? 'Size ' . $item['size'] : null,
            filled($item['flavor'] ?? null) ? 'Flavor ' . $item['flavor'] : null,
            ($item['item_type'] ?? 'catalog') === 'custom' && filled($item['design_description'] ?? null) ? 'Custom brief included' : null,
        ]);

        return [
            'description' => $item['description'] ?? '',
            'dedicationMessage' => $item['dedication_message'] ?? null,
            'designDescription' => $item['design_description'] ?? null,
            'id' => sprintf('%s-item-%d', $pendingOrder['id'], $index + 1),
            'product_id' => $item['product_id'] ?? null,
            'image' => $this->resolveImageUrl($item['image_url'] ?? null) ?: asset('images/bakerdan/Cake_Celebration.png'),
            'line_total' => round($unitPrice * $quantity, 2),
            'line_total_label' => 'PHP ' . number_format($unitPrice * $quantity, 2),
            'name' => $item['product_name'] ?? 'Bakerdan item',
            'quantity' => $quantity,
            'size' => $item['size'] ?? null,
            'flavor' => $item['flavor'] ?? null,
            'source' => ($item['item_type'] ?? 'catalog') === 'custom' ? 'custom' : 'catalog',
            'summary' => implode(' | ', $detailParts),
            'unit_price' => $unitPrice,
            'unit_price_label' => 'PHP ' . number_format($unitPrice, 2),
        ];
    }

    private function paymentMethodLabel(?string $paymentMethod): string
    {
        return match (strtolower((string) $paymentMethod)) {
            'gcash' => 'GCash',
            'maya', 'paymaya' => 'Maya',
            'cash' => 'Cash',
            default => 'Payment pending',
        };
    }

    private function resolveImageUrl(?string $imagePath): ?string
    {
        if (! $imagePath) {
            return null;
        }

        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://') || str_starts_with($imagePath, '/')) {
            return $imagePath;
        }

        return asset('storage/' . ltrim($imagePath, '/'));
    }

    private function notifyCustomerPaymentConfirmed(Order $order): void
    {
        if (! $order->user_id) {
            return;
        }

        CustomerNotification::query()->create([
            'user_id' => $order->user_id,
            'type' => 'order',
            'title' => 'Payment confirmed',
            'message' => "Order #{$order->id} was paid successfully and sent to BakerDan for processing.",
            'payload' => [
                'action' => 'open_order',
                'order_id' => $order->id,
                'payment_status' => $order->payment_status,
                'source' => 'pending_order_payment_confirmed',
            ],
            'image_url' => null,
        ]);
    }
}
