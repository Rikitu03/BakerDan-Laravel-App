<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\PendingOrderService;
use App\Services\PayMongoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayMongoWebhookController extends Controller
{
    public function handle(Request $request, PayMongoService $payMongo, PendingOrderService $pendingOrders): JsonResponse
    {
        if (! $payMongo->shouldProcessWebhook()) {
            return response()->json([
                'success' => true,
                'message' => 'PayMongo webhook secret is not configured. Event ignored.',
            ], 202);
        }

        $payload = $request->getContent();
        $event = $request->json()->all();
        $liveMode = (bool) data_get($event, 'data.attributes.livemode', false);

        if (! $payMongo->verifyWebhookSignature($request->header('Paymongo-Signature'), $payload, $liveMode)) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid PayMongo webhook signature.',
            ], 400);
        }

        if (data_get($event, 'data.attributes.type') === 'checkout_session.payment.paid') {
            $checkoutSession = data_get($event, 'data.attributes.data');
            $orderId = (int) data_get($checkoutSession, 'attributes.metadata.order_id');
            $sessionId = data_get($checkoutSession, 'id');
            $pendingOrderId = data_get($checkoutSession, 'attributes.metadata.pending_order_id');

            if (is_array($checkoutSession) && ($orderId > 0 || filled($sessionId))) {
                $order = Order::query()
                    ->where(function ($query) use ($orderId, $sessionId): void {
                        if (filled($sessionId)) {
                            $query->where('payment_session_id', $sessionId);
                        }

                        if ($orderId > 0) {
                            $query->orWhereKey($orderId);
                        }
                    })
                    ->first();

                if ($order) {
                    $payMongo->syncOrderPayment($order, $checkoutSession);
                } else {
                    $pendingOrder = null;

                    if (filled($pendingOrderId)) {
                        $pendingOrder = $pendingOrders->find((string) $pendingOrderId);
                    }

                    if (! $pendingOrder && filled($sessionId)) {
                        $pendingOrder = $pendingOrders->findBySessionId((string) $sessionId);
                    }

                    if ($pendingOrder) {
                        $pendingOrders->materializePaidOrder($pendingOrder, $checkoutSession, $payMongo);
                    }
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Webhook received.',
        ]);
    }
}
