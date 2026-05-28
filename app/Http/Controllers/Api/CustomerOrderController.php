<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\PendingOrderService;
use App\Services\PayMongoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CustomerOrderController extends Controller
{
    private const HISTORY_LIMIT = 12;

    public function index(Request $request, PendingOrderService $pendingOrders, PayMongoService $payMongo): JsonResponse
    {
        $userId = (int) Auth::id();
        $currentStatuses = $this->currentStatuses();
        $highlightSync = $this->syncHighlightedPaymentReturn($request, $pendingOrders, $payMongo, $userId);

        $currentOrders = Order::query()
            ->with(['items.product', 'promo'])
            ->where('user_id', $userId)
            ->whereIn('status', $currentStatuses)
            ->latest('id')
            ->get();
        $historyOrders = Order::query()
            ->with(['items.product', 'reviews', 'promo'])
            ->where('user_id', $userId)
            ->whereNotIn('status', $currentStatuses)
            ->withCount([
                'items as custom_item_count' => fn ($query) => $query->where('item_type', 'custom'),
            ])
            ->withSum('items as item_quantity_total', 'quantity')
            ->latest('id')
            ->limit(self::HISTORY_LIMIT)
            ->get();
        $pendingOrderPayloads = collect($pendingOrders->allForUser($userId))
            ->map(fn (array $pendingOrder): array => $pendingOrders->serializeForCustomer($pendingOrder));
        $currentOrderPayloads = $currentOrders->map(fn (Order $order): array => $this->serializeOrder($order));
        $historyOrderPayloads = $historyOrders->map(fn (Order $order): array => $this->serializeOrderSummary($order));
        $serializedOrders = $currentOrderPayloads
            ->concat($pendingOrderPayloads)
            ->sortByDesc(fn (array $order): string => (string) ($order['placed_at'] ?? ''))
            ->concat($historyOrderPayloads)
            ->values();

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $serializedOrders,
                'summary' => [
                    'current_orders' => $serializedOrders->where('is_current', true)->count(),
                    'custom_orders' => $serializedOrders->where('contains_custom', true)->count(),
                    'pending_payment' => $serializedOrders->filter(function (array $order): bool {
                        return ($order['is_current'] ?? false) && in_array($order['payment_status'] ?? null, ['pending', 'unpaid'], true);
                    })->count(),
                ],
                'highlight' => $highlightSync,
            ],
        ]);
    }

    private function syncHighlightedPaymentReturn(
        Request $request,
        PendingOrderService $pendingOrders,
        PayMongoService $payMongo,
        int $userId,
    ): ?array {
        $pendingOrderId = trim((string) $request->query('highlight', ''));

        if ($request->query('payment_return') !== 'success' || ! str_starts_with($pendingOrderId, 'pending-')) {
            return null;
        }

        $resolvedOrderId = $pendingOrders->resolveMaterializedOrderId($pendingOrderId);
        if ($resolvedOrderId) {
            return [
                'materialized' => true,
                'order_id' => $resolvedOrderId,
                'replaced_pending_order_id' => $pendingOrderId,
            ];
        }

        $pendingOrder = $pendingOrders->findForUser($userId, $pendingOrderId);
        if (! $pendingOrder || blank($pendingOrder['payment_session_id'] ?? null)) {
            return null;
        }

        try {
            $checkoutSession = $payMongo->retrieveCheckoutSession((string) $pendingOrder['payment_session_id'], 6);
            $paymentStatus = $payMongo->checkoutSessionPaymentStatus($checkoutSession);

            if ($paymentStatus === 'paid') {
                $order = $pendingOrders->materializePaidOrder($pendingOrder, $checkoutSession, $payMongo);

                return [
                    'materialized' => true,
                    'order_id' => (int) $order->id,
                    'replaced_pending_order_id' => $pendingOrderId,
                ];
            }

            $pendingOrders->syncCheckoutSession($pendingOrder, $checkoutSession, $paymentStatus);

            return [
                'materialized' => false,
                'order_id' => null,
                'replaced_pending_order_id' => null,
            ];
        } catch (Throwable $exception) {
            report($exception);

            return [
                'materialized' => false,
                'order_id' => null,
                'replaced_pending_order_id' => null,
                'sync_error' => $exception->getMessage(),
            ];
        }
    }

    public function purchaseHistory(): JsonResponse
    {
        $orders = Order::query()
            ->with(['items.product', 'reviews', 'promo'])
            ->where('user_id', Auth::id())
            ->where('payment_status', 'paid')
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'purchases' => $orders->map(fn (Order $order): array => $this->serializeOrder($order))->values(),
                'total_spent' => round($orders->sum('total_amount'), 2),
                'total_orders' => $orders->count(),
            ],
        ]);
    }

    public function cancel(Order $order): JsonResponse
    {
        abort_unless($order->user_id === Auth::id(), 403);

        if (! $this->canCancel($order)) {
            $message = $order->payment_status === 'paid'
                ? 'Paid orders can no longer be cancelled.'
                : (($order->status !== 'pending')
                    ? 'Only pending orders can be cancelled.'
                    : "Can't cancel order");

            return response()->json([
                'success' => false,
                'message' => $message,
            ], 422);
        }

        $order->update([
            'status' => 'cancelled',
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Your order has been cancelled successfully.',
            'data' => [
                'order' => $this->serializeOrder($order->fresh(['items.product'])),
            ],
        ]);
    }

    public function cancelPending(string $pendingOrderId, PendingOrderService $pendingOrders): JsonResponse
    {
        if ($pendingOrders->resolveMaterializedOrderId($pendingOrderId)) {
            return response()->json([
                'success' => false,
                'message' => "Can't cancel order",
            ], 422);
        }

        if (! $pendingOrders->cancelForUser((int) Auth::id(), $pendingOrderId)) {
            return response()->json([
                'success' => false,
                'message' => "Can't cancel order",
            ], 422);
        }

        return response()->json([
            'success' => true,
            'message' => 'Your pending order has been cancelled successfully.',
            'data' => [
                'cancelled_order_id' => $pendingOrderId,
            ],
        ]);
    }

    private function serializeOrder(Order $order): array
    {
        $containsCustom = $order->items->contains(fn (OrderItem $item): bool => $item->item_type === 'custom');

        return [
            'can_cancel' => $this->canCancel($order),
            'can_continue_payment' => $this->canContinuePayment($order),
            'can_refresh_payment' => filled($order->payment_session_id),
            'checkout_url' => $order->payment_checkout_url,
            'contains_custom' => $containsCustom,
            'flow_status' => $this->flowStatus($order->status),
            'flow_status_label' => $this->flowStatusLabel($order->status),
            'id' => $order->id,
            'can_review' => in_array($order->status, ['delivered', 'completed']),
            'is_current' => $this->isCurrentOrder($order),
            'item_count' => $order->items->sum('quantity'),
            'items' => $order->items->map(fn (OrderItem $item): array => $this->serializeOrderItem($item, $order))->values()->all(),
            'payment' => [
                'checkout_url' => $order->payment_checkout_url,
                'gateway_status' => data_get($order->payment_metadata, 'payment_intent_status')
                    ?: data_get($order->payment_metadata, 'checkout_session_status'),
                'provider' => $order->payment_provider,
                'reference' => $order->payment_reference,
                'session_id' => $order->payment_session_id,
                'status' => $order->payment_status,
            ],
            'payment_method' => $order->payment_method,
            'payment_method_label' => $this->paymentMethodLabel($order->payment_method),
            'payment_paid_at' => $order->payment_paid_at?->toIso8601String(),
            'payment_status' => $order->payment_status,
            'payment_status_label' => $this->paymentStatusLabel($order->payment_status),
            'placed_at' => $order->created_at?->toIso8601String(),
            'placed_at_label' => $order->created_at?->format('M d, Y h:i A'),
            'reference' => $order->payment_reference,
            'shipping_address' => $order->shipping_address,
            'status' => $order->status,
            'status_label' => $this->customerStatusLabel($order->status, $containsCustom),
            'status_note' => $this->statusNote($order, $containsCustom),
            'total_amount' => (float) $order->total_amount,
            'total_amount_label' => 'PHP ' . number_format((float) $order->total_amount, 2),
            'promo_id' => $order->promo_id,
            'discount_amount' => (float) $order->discount_amount,
            'promo_code' => $order->promo?->code,
        ];
    }

    private function serializeOrderSummary(Order $order): array
    {
        $containsCustom = (int) ($order->custom_item_count ?? 0) > 0;
        $itemCount = (int) round((float) ($order->item_quantity_total ?? 0));

        return [
            'can_cancel' => $this->canCancel($order),
            'can_continue_payment' => $this->canContinuePayment($order),
            'can_refresh_payment' => filled($order->payment_session_id),
            'can_review' => in_array($order->status, ['delivered', 'completed']),
            'checkout_url' => $order->payment_checkout_url,
            'contains_custom' => $containsCustom,
            'flow_status' => $this->flowStatus($order->status),
            'flow_status_label' => $this->flowStatusLabel($order->status),
            'id' => $order->id,
            'is_current' => false,
            'item_count' => $itemCount,
            'items' => $order->relationLoaded('items')
                ? $order->items->map(fn (OrderItem $item): array => $this->serializeOrderItem($item, $order))->values()->all()
                : [],
            'payment' => [
                'checkout_url' => $order->payment_checkout_url,
                'gateway_status' => data_get($order->payment_metadata, 'payment_intent_status')
                    ?: data_get($order->payment_metadata, 'checkout_session_status'),
                'provider' => $order->payment_provider,
                'reference' => $order->payment_reference,
                'session_id' => $order->payment_session_id,
                'status' => $order->payment_status,
            ],
            'payment_method' => $order->payment_method,
            'payment_method_label' => $this->paymentMethodLabel($order->payment_method),
            'payment_paid_at' => $order->payment_paid_at?->toIso8601String(),
            'payment_status' => $order->payment_status,
            'payment_status_label' => $this->paymentStatusLabel($order->payment_status),
            'placed_at' => $order->created_at?->toIso8601String(),
            'placed_at_label' => $order->created_at?->format('M d, Y h:i A'),
            'reference' => $order->payment_reference,
            'shipping_address' => $order->shipping_address,
            'status' => $order->status,
            'status_label' => $this->customerStatusLabel($order->status, $containsCustom),
            'status_note' => $this->statusNote($order, $containsCustom),
            'total_amount' => (float) $order->total_amount,
            'total_amount_label' => 'PHP ' . number_format((float) $order->total_amount, 2),
            'promo_id' => $order->promo_id,
            'discount_amount' => (float) $order->discount_amount,
            'promo_code' => $order->promo?->code,
        ];
    }

    private function serializeOrderItem(OrderItem $item, ?Order $order = null): array
    {
        $unitPrice = (float) $item->price;
        $detailParts = array_filter([
            $item->size ? 'Size ' . $item->size : null,
            $item->flavor ? 'Flavor ' . $item->flavor : null,
            $item->item_type === 'custom' && filled($item->design_description) ? 'Custom brief included' : null,
        ]);

        $isReviewed = false;
        if ($order && $item->product_id) {
            $isReviewed = $order->reviews->where('product_id', $item->product_id)->isNotEmpty();
        }

        return [
            'description' => $item->description,
            'dedicationMessage' => $item->dedication_message,
            'designDescription' => $item->design_description,
            'id' => $item->id,
            'product_id' => $item->product_id,
            'image' => $this->resolveImageUrl($item->image_url) ?: asset('images/bakerdan/Cake_Celebration.png'),
            'line_total' => round($unitPrice * $item->quantity, 2),
            'line_total_label' => 'PHP ' . number_format($unitPrice * $item->quantity, 2),
            'name' => $item->product?->product_name ?? $item->product_name ?? 'Bakerdan item',
            'quantity' => $item->quantity,
            'size' => $item->size,
            'flavor' => $item->flavor,
            'source' => $item->item_type === 'custom' ? 'custom' : 'catalog',
            'summary' => implode(' | ', $detailParts),
            'unit_price' => $unitPrice,
            'unit_price_label' => 'PHP ' . number_format($unitPrice, 2),
            'is_reviewed' => $isReviewed,
        ];
    }

    private function canCancel(Order $order): bool
    {
        return $order->status === 'pending' && $order->payment_status !== 'paid';
    }

    private function canContinuePayment(Order $order): bool
    {
        return filled($order->payment_checkout_url)
            && in_array($order->payment_status, ['pending', 'unpaid'], true)
            && $order->status !== 'cancelled';
    }

    private function isCurrentOrder(Order $order): bool
    {
        return in_array($order->status, $this->currentStatuses(), true);
    }

    private function currentStatuses(): array
    {
        return ['pending', 'processing', 'shipped'];
    }

    private function flowStatus(string $status): string
    {
        return match ($status) {
            'processing' => 'preparing',
            'shipped' => 'ready',
            'delivered' => 'completed',
            default => $status,
        };
    }

    private function flowStatusLabel(string $status): string
    {
        return match ($status) {
            'processing' => 'Preparing',
            'shipped' => 'Ready',
            'delivered' => 'Completed',
            default => ucfirst($status),
        };
    }

    private function customerStatusLabel(string $status, bool $containsCustom): string
    {
        return match ($status) {
            'pending' => $containsCustom ? 'Pending Review' : 'Pending',
            'processing' => 'Preparing',
            'shipped' => $containsCustom ? 'Ready for Release' : 'Ready',
            'delivered' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($status),
        };
    }

    private function paymentStatusLabel(?string $status): string
    {
        return match ($status) {
            'paid' => 'Paid',
            'failed' => 'Failed',
            'expired' => 'Expired',
            default => 'Unpaid',
        };
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

    private function statusNote(Order $order, bool $containsCustom): string
    {
        if ($order->status === 'cancelled') {
            return 'This order was cancelled before production started.';
        }

        if ($order->payment_status !== 'paid') {
            return 'Complete payment to keep this order active and visible in the bakery queue.';
        }

        if ($order->status === 'pending') {
            return $containsCustom
                ? 'Payment is confirmed. The bakery will review your custom brief before production starts.'
                : 'Payment is confirmed. Your order is waiting for the bakery to start preparing it.';
        }

        if ($order->status === 'processing') {
            return 'The bakery is actively preparing this order.';
        }

        if ($order->status === 'shipped') {
            return 'This order is ready for release or pickup.';
        }

        return 'This order has been completed.';
    }

    private function resolveImageUrl(?string $imagePath): ?string
    {
        if (! $imagePath) {
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

        return Storage::disk('s3')->url($imagePath);
    }
}
