<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class CustomerOrderController extends Controller
{
    public function index(): JsonResponse
    {
        $orders = Order::query()
            ->with(['items.product'])
            ->where('user_id', Auth::id())
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'orders' => $orders->map(fn (Order $order): array => $this->serializeOrder($order))->values(),
                'summary' => [
                    'current_orders' => $orders->filter(fn (Order $order): bool => $this->isCurrentOrder($order))->count(),
                    'custom_orders' => $orders->filter(fn (Order $order): bool => $order->items->contains(fn (OrderItem $item): bool => $item->item_type === 'custom'))->count(),
                    'pending_payment' => $orders->filter(function (Order $order): bool {
                        return $this->isCurrentOrder($order) && in_array($order->payment_status, ['pending', 'unpaid'], true);
                    })->count(),
                ],
            ],
        ]);
    }

    public function cancel(Order $order): JsonResponse
    {
        abort_unless($order->user_id === Auth::id(), 403);

        if ($order->payment_status === 'paid') {
            return response()->json([
                'success' => false,
                'message' => 'Paid orders can no longer be cancelled.',
            ], 422);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending orders can be cancelled.',
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
            'is_current' => $this->isCurrentOrder($order),
            'item_count' => $order->items->sum('quantity'),
            'items' => $order->items->map(fn (OrderItem $item): array => $this->serializeOrderItem($item))->values()->all(),
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
        ];
    }

    private function serializeOrderItem(OrderItem $item): array
    {
        $unitPrice = (float) $item->price;
        $detailParts = array_filter([
            $item->size ? 'Size ' . $item->size : null,
            $item->flavor ? 'Flavor ' . $item->flavor : null,
            $item->item_type === 'custom' && filled($item->design_description) ? 'Custom brief included' : null,
        ]);

        return [
            'description' => $item->description,
            'dedicationMessage' => $item->dedication_message,
            'designDescription' => $item->design_description,
            'id' => $item->id,
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
        return in_array($order->status, ['pending', 'processing', 'shipped'], true);
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

        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://') || str_starts_with($imagePath, '/')) {
            return $imagePath;
        }

        if (Storage::disk('public')->exists($imagePath)) {
            return Storage::disk('public')->url($imagePath);
        }

        return asset('storage/' . ltrim($imagePath, '/'));
    }
}
