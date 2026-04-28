<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
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

    private function serializeOrder(Order $order): array
    {
        return [
            'contains_custom' => $order->items->contains(fn (OrderItem $item) => $item->item_type === 'custom'),
            'checkout_url' => $order->payment_checkout_url,
            'custom_item_count' => $order->items->where('item_type', 'custom')->count(),
            'id' => $order->id,
            'item_count' => $order->items->sum('quantity'),
            'paid_at' => $order->payment_paid_at?->toIso8601String(),
            'payment_method' => $order->payment_method,
            'payment_provider' => $order->payment_provider,
            'payment_status' => $order->payment_status,
            'reference' => $order->payment_reference,
            'status' => $order->status,
            'total_amount' => (float) $order->total_amount,
        ];
    }
}
