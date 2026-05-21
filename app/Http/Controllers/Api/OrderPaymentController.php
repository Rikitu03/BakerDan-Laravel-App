<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\PendingOrderService;
use App\Services\PayMongoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

class OrderPaymentController extends Controller
{
    public function show(Order $order, PayMongoService $payMongo): JsonResponse
    {
        abort_unless($order->user_id === Auth::id(), 403);

        $syncError = null;

        if ($order->payment_provider === 'paymongo' && filled($order->payment_session_id)) {
            try {
                $checkoutSession = $payMongo->retrieveCheckoutSession($order->payment_session_id);
                $order = $payMongo->syncOrderPayment($order, $checkoutSession);
            } catch (Throwable $exception) {
                report($exception);
                $syncError = $exception->getMessage();
                $order->loadMissing('items');
            }
        } else {
            $order->loadMissing('items');
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $this->serializeOrder($order),
                'payment' => [
                    'checkout_url' => $order->payment_checkout_url,
                    'gateway_status' => data_get($order->payment_metadata, 'payment_intent_status')
                        ?: data_get($order->payment_metadata, 'checkout_session_status'),
                    'provider' => $order->payment_provider,
                    'reference' => $order->payment_reference,
                    'session_id' => $order->payment_session_id,
                    'status' => $order->payment_status,
                    'sync_error' => $syncError,
                ],
            ],
        ]);
    }

    public function showPending(string $pendingOrderId, PendingOrderService $pendingOrders, PayMongoService $payMongo): JsonResponse
    {
        $resolvedOrderId = $pendingOrders->resolveMaterializedOrderId($pendingOrderId);

        if ($resolvedOrderId) {
            $order = Order::query()
                ->with(['items.product'])
                ->where('user_id', Auth::id())
                ->whereKey($resolvedOrderId)
                ->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'order' => $this->serializeOrder($order),
                    'payment' => [
                        'checkout_url' => $order->payment_checkout_url,
                        'gateway_status' => data_get($order->payment_metadata, 'payment_intent_status')
                            ?: data_get($order->payment_metadata, 'checkout_session_status'),
                        'provider' => $order->payment_provider,
                        'reference' => $order->payment_reference,
                        'session_id' => $order->payment_session_id,
                        'status' => $order->payment_status,
                        'sync_error' => null,
                    ],
                    'materialized' => true,
                    'replaced_pending_order_id' => $pendingOrderId,
                ],
            ]);
        }

        $pendingOrder = $pendingOrders->findForUser((int) Auth::id(), $pendingOrderId);

        if (! $pendingOrder) {
            return response()->json([
                'success' => false,
                'message' => 'This pending payment window has expired or the order draft no longer exists.',
            ], 410);
        }

        if ($pendingOrders->isExpired($pendingOrder)) {
            $pendingOrders->forget($pendingOrderId);

            return response()->json([
                'success' => false,
                'message' => 'This pending payment window has expired and the order draft was removed automatically.',
            ], 410);
        }

        $syncError = null;

        if (filled($pendingOrder['payment_session_id'] ?? null)) {
            try {
                $checkoutSession = $payMongo->retrieveCheckoutSession((string) $pendingOrder['payment_session_id']);
                $paymentStatus = $payMongo->checkoutSessionPaymentStatus($checkoutSession);

                if ($paymentStatus === 'paid') {
                    $order = $pendingOrders->materializePaidOrder($pendingOrder, $checkoutSession, $payMongo);

                    return response()->json([
                        'success' => true,
                        'data' => [
                            'order' => $this->serializeOrder($order),
                            'payment' => [
                                'checkout_url' => $order->payment_checkout_url,
                                'gateway_status' => data_get($order->payment_metadata, 'payment_intent_status')
                                    ?: data_get($order->payment_metadata, 'checkout_session_status'),
                                'provider' => $order->payment_provider,
                                'reference' => $order->payment_reference,
                                'session_id' => $order->payment_session_id,
                                'status' => $order->payment_status,
                                'sync_error' => null,
                            ],
                            'materialized' => true,
                            'replaced_pending_order_id' => $pendingOrderId,
                        ],
                    ]);
                }

                $pendingOrder = $pendingOrders->syncCheckoutSession($pendingOrder, $checkoutSession);
            } catch (Throwable $exception) {
                report($exception);
                $syncError = $exception->getMessage();
            }
        }

        return response()->json([
            'success' => true,
            'data' => [
                'order' => $pendingOrders->serializeForCustomer($pendingOrder),
                'payment' => $pendingOrders->paymentPayloadForCustomer($pendingOrder, $syncError),
                'materialized' => false,
                'replaced_pending_order_id' => null,
            ],
        ]);
    }

    private function serializeOrder(Order $order): array
    {
        $containsCustom = $order->items->contains(fn (OrderItem $item) => $item->item_type === 'custom');

        return [
            'can_cancel' => $order->status === 'pending' && $order->payment_status !== 'paid',
            'can_continue_payment' => filled($order->payment_checkout_url)
                && in_array($order->payment_status, ['pending', 'unpaid'], true)
                && $order->status !== 'cancelled',
            'can_refresh_payment' => filled($order->payment_session_id),
            'contains_custom' => $containsCustom,
            'checkout_url' => $order->payment_checkout_url,
            'custom_item_count' => $order->items->where('item_type', 'custom')->count(),
            'flow_status' => match ($order->status) {
                'processing' => 'preparing',
                'shipped' => 'ready',
                'delivered' => 'completed',
                default => $order->status,
            },
            'id' => $order->id,
            'item_count' => $order->items->sum('quantity'),
            'paid_at' => $order->payment_paid_at?->toIso8601String(),
            'payment_method' => $order->payment_method,
            'payment_method_label' => match (strtolower((string) $order->payment_method)) {
                'gcash' => 'GCash',
                'maya', 'paymaya' => 'Maya',
                'cash' => 'Cash',
                default => 'Payment pending',
            },
            'payment_provider' => $order->payment_provider,
            'payment_status' => $order->payment_status,
            'payment_status_label' => match ($order->payment_status) {
                'paid' => 'Paid',
                'failed' => 'Failed',
                'expired' => 'Expired',
                default => 'Unpaid',
            },
            'reference' => $order->payment_reference,
            'status' => $order->status,
            'status_label' => match ($order->status) {
                'pending' => $containsCustom ? 'Pending Review' : 'Pending',
                'processing' => 'Preparing',
                'shipped' => $containsCustom ? 'Ready for Release' : 'Ready',
                'delivered' => 'Completed',
                'cancelled' => 'Cancelled',
                default => ucfirst($order->status),
            },
            'total_amount' => (float) $order->total_amount,
        ];
    }
}
