<?php

namespace App\Services;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;

class PayMongoService
{
    public function createCheckoutSession(Order $order): array
    {
        $this->ensureConfigured();

        $order->loadMissing(['items', 'user.detail']);
        $referenceNumber = $order->payment_reference ?: sprintf('BD-%06d-%s', $order->id, Str::upper(Str::random(6)));

        return $this->createCheckoutSessionFromAttributes([
            'cancel_url' => $this->cancelUrl($order),
            'description' => "Bakerdan order #{$order->id}",
            'line_items' => $this->lineItems($order),
            'metadata' => [
                'order_id' => (string) $order->id,
                'payment_method' => $this->normalizePaymentMethod($order->payment_method),
            ],
            'payment_method_types' => [
                $this->payMongoPaymentMethod($order->payment_method),
            ],
            'reference_number' => $referenceNumber,
            'send_email_receipt' => false,
            'show_description' => true,
            'show_line_items' => true,
            'success_url' => $this->successUrl($order),
        ]);
    }

    public function createCheckoutSessionFromPendingOrder(array $pendingOrder): array
    {
        $this->ensureConfigured();

        $referenceNumber = $pendingOrder['payment_reference'] ?? sprintf('BD-PND-%s', Str::upper(Str::random(8)));

        return $this->createCheckoutSessionFromAttributes([
            'cancel_url' => $this->pendingOrderCancelUrl($pendingOrder),
            'description' => "Bakerdan pending order {$pendingOrder['id']}",
            'line_items' => $this->pendingOrderLineItems($pendingOrder),
            'metadata' => array_filter([
                'pending_order_id' => (string) ($pendingOrder['id'] ?? ''),
                'user_id' => isset($pendingOrder['user_id']) ? (string) $pendingOrder['user_id'] : null,
                'payment_method' => $this->normalizePaymentMethod($pendingOrder['payment_method'] ?? null),
            ], fn ($value) => $value !== null && $value !== ''),
            'payment_method_types' => [
                $this->payMongoPaymentMethod($pendingOrder['payment_method'] ?? null),
            ],
            'reference_number' => $referenceNumber,
            'send_email_receipt' => false,
            'show_description' => true,
            'show_line_items' => true,
            'success_url' => $this->pendingOrderSuccessUrl($pendingOrder),
        ]);
    }

    public function retrieveCheckoutSession(string $sessionId, int $timeoutSeconds = 10): array
    {
        $this->ensureConfigured();

        $response = $this->client($timeoutSeconds)->get('/checkout_sessions/' . $sessionId);
        $this->throwOnError($response, 'Unable to retrieve the PayMongo checkout session.');

        $checkoutSession = $response->json('data');

        if (!is_array($checkoutSession) || blank($checkoutSession['id'] ?? null)) {
            throw new RuntimeException('PayMongo returned an incomplete checkout session payload.');
        }

        return $checkoutSession;
    }

    public function syncOrderPayment(Order $order, array $checkoutSession): Order
    {
        $payment = collect(data_get($checkoutSession, 'attributes.payments', []))->first();
        $paymentAttributes = is_array($payment) ? ($payment['attributes'] ?? []) : [];
        $paymentIntentStatus = data_get($checkoutSession, 'attributes.payment_intent.attributes.status');
        $checkoutSessionStatus = data_get($checkoutSession, 'attributes.status');
        $resolvedStatus = $this->resolvePaymentStatus($checkoutSessionStatus, $paymentIntentStatus, $paymentAttributes['status'] ?? null);
        $existingMetadata = is_array($order->payment_metadata) ? $order->payment_metadata : [];

        $order->fill([
            'payment_status' => $resolvedStatus,
            'payment_provider' => 'paymongo',
            'payment_reference' => data_get($checkoutSession, 'attributes.reference_number', $order->payment_reference),
            'payment_session_id' => $checkoutSession['id'] ?? $order->payment_session_id,
            'payment_checkout_url' => data_get($checkoutSession, 'attributes.checkout_url', $order->payment_checkout_url),
            'payment_paid_at' => $resolvedStatus === 'paid'
                ? $this->timestampToCarbon($paymentAttributes['paid_at'] ?? null) ?? $order->payment_paid_at ?? now()
                : $order->payment_paid_at,
            'payment_metadata' => array_merge($existingMetadata, array_filter([
                'checkout_session_status' => $checkoutSessionStatus,
                'last_synced_at' => now()->toIso8601String(),
                'payment_id' => $payment['id'] ?? null,
                'payment_intent_id' => data_get($checkoutSession, 'attributes.payment_intent.id'),
                'payment_intent_status' => $paymentIntentStatus,
                'payment_method_used' => data_get($checkoutSession, 'attributes.payment_method_used'),
                'payment_status' => $paymentAttributes['status'] ?? null,
            ], fn ($value) => $value !== null && $value !== '')),
        ]);

        $order->save();

        return $order->fresh(['items']);
    }

    public function shouldProcessWebhook(): bool
    {
        return filled(config('services.paymongo.webhook_secret'));
    }

    public function verifyWebhookSignature(?string $signatureHeader, string $payload, bool $liveMode = false): bool
    {
        $secret = config('services.paymongo.webhook_secret');

        if (blank($secret) || blank($signatureHeader)) {
            return false;
        }

        $segments = [];

        foreach (explode(',', $signatureHeader) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, null);

            if ($key !== null && $value !== null) {
                $segments[$key] = $value;
            }
        }

        $timestamp = $segments['t'] ?? null;
        $providedSignature = $liveMode ? ($segments['li'] ?? null) : ($segments['te'] ?? null);

        if (blank($timestamp) || blank($providedSignature)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        return hash_equals($expectedSignature, $providedSignature);
    }

    public function normalizePaymentMethod(?string $paymentMethod): string
    {
        return match (strtolower((string) $paymentMethod)) {
            'paymaya', 'maya' => 'maya',
            default => 'gcash',
        };
    }

    public function checkoutSessionPaymentStatus(array $checkoutSession): string
    {
        $payment = collect(data_get($checkoutSession, 'attributes.payments', []))->first();
        $paymentAttributes = is_array($payment) ? ($payment['attributes'] ?? []) : [];

        return $this->resolvePaymentStatus(
            data_get($checkoutSession, 'attributes.status'),
            data_get($checkoutSession, 'attributes.payment_intent.attributes.status'),
            $paymentAttributes['status'] ?? null,
        );
    }

    private function ensureConfigured(): void
    {
        if (blank(config('services.paymongo.secret_key'))) {
            throw new RuntimeException('PayMongo is not configured yet. Add your PAYMONGO_SECRET_KEY to continue.');
        }
    }

    private function client(int $timeoutSeconds = 15)
    {
        $timeoutSeconds = max(5, $timeoutSeconds);

        return Http::acceptJson()
            ->asJson()
            ->baseUrl(rtrim((string) config('services.paymongo.base_url', 'https://api.paymongo.com/v1'), '/'))
            ->connectTimeout(min(5, $timeoutSeconds))
            ->timeout($timeoutSeconds)
            ->withBasicAuth((string) config('services.paymongo.secret_key'), '');
    }

    private function cancelUrl(Order $order): string
    {
        return url('/customer/orders') . '?highlight=' . $order->id . '&payment_return=cancelled';
    }

    private function successUrl(Order $order): string
    {
        return url('/customer/orders') . '?highlight=' . $order->id . '&payment_return=success';
    }

    private function pendingOrderCancelUrl(array $pendingOrder): string
    {
        return url('/customer/orders') . '?highlight=' . urlencode((string) ($pendingOrder['id'] ?? '')) . '&payment_return=cancelled';
    }

    private function pendingOrderSuccessUrl(array $pendingOrder): string
    {
        return url('/customer/orders') . '?highlight=' . urlencode((string) ($pendingOrder['id'] ?? '')) . '&payment_return=success';
    }

    private function lineItems(Order $order): array
    {
        $items = $order->items->map(function ($item): array {
            return [
                'amount' => $this->toCentavos((float) $item->price),
                'currency' => 'PHP',
                'description' => Str::limit((string) ($item->description ?: $item->product_name ?: 'Bakerdan item'), 255, ''),
                'name' => Str::limit((string) ($item->product_name ?: 'Bakerdan item'), 120, ''),
                'quantity' => (int) $item->quantity,
            ];
        })->values();

        $itemsSubtotal = $order->items->sum(fn ($item) => (float) $item->price * (int) $item->quantity);
        $shippingLineItem = $this->shippingLineItem((float) $order->total_amount, (float) $itemsSubtotal);

        if ($shippingLineItem) {
            $items->push($shippingLineItem);
        }

        return $items->all();
    }

    private function pendingOrderLineItems(array $pendingOrder): array
    {
        $items = collect($pendingOrder['items'] ?? [])->map(function (array $item): array {
            return [
                'amount' => $this->toCentavos((float) ($item['unit_price'] ?? 0)),
                'currency' => 'PHP',
                'description' => Str::limit((string) ($item['description'] ?? $item['product_name'] ?? 'Bakerdan item'), 255, ''),
                'name' => Str::limit((string) ($item['product_name'] ?? 'Bakerdan item'), 120, ''),
                'quantity' => (int) ($item['quantity'] ?? 1),
            ];
        })->values();
        $itemsSubtotal = collect($pendingOrder['items'] ?? [])
            ->sum(fn (array $item) => (float) ($item['unit_price'] ?? 0) * (int) ($item['quantity'] ?? 1));
        $shippingLineItem = $this->shippingLineItem((float) ($pendingOrder['total_amount'] ?? 0), (float) $itemsSubtotal);

        if ($shippingLineItem) {
            $items->push($shippingLineItem);
        }

        return $items->all();
    }

    private function shippingLineItem(float $totalAmount, float $itemsSubtotal): ?array
    {
        $shippingAmount = round($totalAmount - $itemsSubtotal, 2);

        if ($shippingAmount <= 0) {
            return null;
        }

        return [
            'amount' => $this->toCentavos($shippingAmount),
            'currency' => 'PHP',
            'description' => 'Fixed shipping fee',
            'name' => 'Shipping Fee',
            'quantity' => 1,
        ];
    }

    private function payMongoPaymentMethod(?string $paymentMethod): string
    {
        return match ($this->normalizePaymentMethod($paymentMethod)) {
            'maya' => 'paymaya',
            default => 'gcash',
        };
    }

    private function resolvePaymentStatus(?string $checkoutSessionStatus, ?string $paymentIntentStatus, ?string $paymentStatus): string
    {
        if ($paymentStatus === 'paid' || $paymentIntentStatus === 'succeeded') {
            return 'paid';
        }

        if (in_array($checkoutSessionStatus, ['expired'], true)) {
            return 'expired';
        }

        if (in_array($paymentStatus, ['failed'], true) || in_array($paymentIntentStatus, ['failed', 'cancelled'], true)) {
            return 'failed';
        }

        return 'pending';
    }

    private function throwOnError(Response $response, string $fallbackMessage): void
    {
        if ($response->successful()) {
            return;
        }

        $details = collect($response->json('errors', []))
            ->map(fn (array $error) => $error['detail'] ?? $error['title'] ?? $error['code'] ?? null)
            ->filter()
            ->implode(' ');

        throw new RuntimeException($details ?: $fallbackMessage);
    }

    private function timestampToCarbon(mixed $timestamp): ?Carbon
    {
        if ($timestamp === null || $timestamp === '') {
            return null;
        }

        return Carbon::createFromTimestamp((int) $timestamp);
    }

    private function toCentavos(float $amount): int
    {
        return (int) round($amount * 100);
    }

    private function createCheckoutSessionFromAttributes(array $attributes): array
    {
        $response = $this->client()->post('/checkout_sessions', [
            'data' => [
                'attributes' => $attributes,
            ],
        ]);
        $this->throwOnError($response, 'Unable to create a PayMongo checkout session right now.');

        $checkoutSession = $response->json('data');

        if (!is_array($checkoutSession) || blank($checkoutSession['id'] ?? null) || blank(data_get($checkoutSession, 'attributes.checkout_url'))) {
            throw new RuntimeException('PayMongo returned an incomplete checkout session response.');
        }

        return $checkoutSession;
    }
}
