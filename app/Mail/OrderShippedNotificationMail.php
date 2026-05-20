<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderShippedNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @var array<string, mixed>
     */
    public array $orderSummary;

    public function __construct(public Order $order)
    {
        $this->order->loadMissing(['items.product', 'user.detail', 'promo']);
        $this->orderSummary = $this->buildOrderSummary($this->order);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your BakerDan Order #' . $this->order->id . ' Is On The Way',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.notification',
            with: [
                'order' => $this->order,
                'summary' => $this->orderSummary,
            ],
        );
    }

    /**
     * @return array<int, mixed>
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    private function buildOrderSummary(Order $order): array
    {
        $items = $order->items
            ->map(function (OrderItem $item): array {
                $unitPrice = (float) $item->price;
                $quantity = (int) $item->quantity;
                $lineTotal = $unitPrice * $quantity;
                $details = array_filter([
                    $item->size ? 'Size: ' . $item->size : null,
                    $item->flavor ? 'Flavor: ' . $item->flavor : null,
                    $item->item_type === 'custom' ? 'Custom order' : null,
                ]);

                return [
                    'name' => $item->product?->product_name ?? $item->product_name ?? 'BakerDan Product',
                    'description' => $item->description,
                    'details' => implode(' | ', $details),
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'unit_price_label' => $this->money($unitPrice),
                    'line_total' => $lineTotal,
                    'line_total_label' => $this->money($lineTotal),
                ];
            })
            ->values()
            ->all();

        $itemsSubtotal = collect($items)->sum('line_total');
        $discount = (float) ($order->discount_amount ?? 0);
        $total = (float) $order->total_amount;
        $shipping = max(0, round($total - $itemsSubtotal + $discount, 2));
        $customerName = $order->user?->detail?->name
            ?: data_get($order->payment_metadata, 'walk_in_customer_name')
            ?: 'BakerDan customer';

        return [
            'customer_name' => $customerName,
            'order_id' => $order->id,
            'placed_at' => $order->created_at?->format('M d, Y h:i A'),
            'items' => $items,
            'item_count' => collect($items)->sum('quantity'),
            'subtotal' => round($itemsSubtotal, 2),
            'subtotal_label' => $this->money($itemsSubtotal),
            'discount' => round($discount, 2),
            'discount_label' => $this->money($discount),
            'shipping' => $shipping,
            'shipping_label' => $this->money($shipping),
            'total' => round($total, 2),
            'total_label' => $this->money($total),
            'payment_method' => $this->paymentMethodLabel($order->payment_method),
            'payment_status' => ucfirst((string) ($order->payment_status ?: 'pending')),
            'shipping_address' => $order->shipping_address,
            'promo_code' => $order->promo?->code,
        ];
    }

    private function money(float $amount): string
    {
        return 'PHP ' . number_format($amount, 2);
    }

    private function paymentMethodLabel(?string $paymentMethod): string
    {
        return match (strtolower((string) $paymentMethod)) {
            'gcash' => 'GCash',
            'maya', 'paymaya' => 'Maya',
            'walk-in', 'walkin', 'cash' => 'Walk-in / Counter',
            default => 'Payment pending',
        };
    }
}
