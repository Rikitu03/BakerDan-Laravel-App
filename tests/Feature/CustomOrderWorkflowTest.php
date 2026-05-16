<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\PayMongoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Support\Facades\Cache;
use Mockery\MockInterface;
use Tests\TestCase;

class CustomOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
        Cache::store('file')->flush();
    }

    public function test_customer_must_provide_design_reference_for_custom_order(): void
    {
        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($customer)
            ->postJson('/api/cart/custom', [
                'size' => 'Medium',
                'quantity' => 2,
                'flavor' => 'Chocolate',
            ]);

        $response
            ->assertStatus(422)
            ->assertJsonValidationErrors(['design_description']);
    }

    public function test_admin_can_progress_order_workflow_and_mark_payment_paid(): void
    {
        $admin = User::query()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $order = Order::query()->create([
            'user_id' => $customer->user_id,
            'total_amount' => 1500,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'gcash',
        ]);

        $this
            ->actingAs($admin)
            ->patchJson("/admin/orders/{$order->id}/status", [
                'status' => 'processing',
            ])
            ->assertOk()
            ->assertJsonPath('data.order.status', 'processing');

        $this
            ->actingAs($admin)
            ->patchJson("/admin/orders/{$order->id}/payment-status", [
                'payment_status' => 'paid',
            ])
            ->assertOk()
            ->assertJsonPath('data.order.payment_status', 'paid');

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
            'payment_status' => 'paid',
        ]);
    }

    public function test_customer_can_view_and_cancel_only_their_own_pending_order(): void
    {
        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $otherCustomer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $order = Order::query()->create([
            'user_id' => $customer->user_id,
            'total_amount' => 950,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'gcash',
        ]);

        Order::query()->create([
            'user_id' => $otherCustomer->user_id,
            'total_amount' => 1250,
            'status' => 'pending',
            'payment_status' => 'pending',
            'payment_method' => 'maya',
        ]);

        $this
            ->actingAs($customer)
            ->getJson('/api/orders')
            ->assertOk()
            ->assertJsonCount(1, 'data.orders')
            ->assertJsonPath('data.orders.0.id', $order->id)
            ->assertJsonPath('data.orders.0.can_cancel', true);

        $this
            ->actingAs($customer)
            ->patchJson("/api/orders/{$order->id}/cancel")
            ->assertOk()
            ->assertJsonPath('data.order.status', 'cancelled')
            ->assertJsonPath('data.order.can_cancel', false);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_customer_cannot_cancel_paid_or_preparing_orders(): void
    {
        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $paidOrder = Order::query()->create([
            'user_id' => $customer->user_id,
            'total_amount' => 850,
            'status' => 'pending',
            'payment_status' => 'paid',
            'payment_method' => 'gcash',
        ]);

        $preparingOrder = Order::query()->create([
            'user_id' => $customer->user_id,
            'total_amount' => 1200,
            'status' => 'processing',
            'payment_status' => 'pending',
            'payment_method' => 'maya',
        ]);

        $this
            ->actingAs($customer)
            ->patchJson("/api/orders/{$paidOrder->id}/cancel")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Paid orders can no longer be cancelled.');

        $this
            ->actingAs($customer)
            ->patchJson("/api/orders/{$preparingOrder->id}/cancel")
            ->assertStatus(422)
            ->assertJsonPath('message', 'Only pending orders can be cancelled.');

        $this->assertDatabaseHas('orders', [
            'id' => $paidOrder->id,
            'status' => 'pending',
            'payment_status' => 'paid',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $preparingOrder->id,
            'status' => 'processing',
            'payment_status' => 'pending',
        ]);
    }

    public function test_customer_cannot_add_cake_catalog_products_directly(): void
    {
        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $cake = Product::query()->create([
            'category' => 'Cakes',
            'product_name' => 'Fondant Cakes',
            'description' => 'Custom-only cake guide entry.',
            'price' => 2500,
            'price_label' => 'PHP 2,500',
            'is_active' => true,
        ]);

        $this
            ->actingAs($customer)
            ->postJson("/api/cart/add/{$cake->id}", [
                'quantity' => 1,
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Cake products are made-to-order only. Please use the cake ordering guide to submit a custom request.');
    }

    public function test_customer_can_submit_guide_based_custom_cake_pricing(): void
    {
        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this
            ->actingAs($customer)
            ->postJson('/api/cart/custom', [
                'size' => '8" x 12"',
                'quantity' => 1,
                'flavor' => 'Chocolate Chiffon',
                'product_name' => 'Sheet Cakes and Number Cakes',
                'guide_section' => 'Soft icing cakes with printed toppers and custom themes',
                'base_price' => 1850,
                'design_description' => 'Blue dinosaur birthday theme with printed topper',
            ])
            ->assertOk()
            ->assertJsonPath('data.item.name', 'Sheet Cakes and Number Cakes')
            ->assertJsonPath('data.item.price', 1850)
            ->assertJsonPath('data.item.size', '8" x 12"')
            ->assertJsonPath('data.item.flavor', 'Chocolate Chiffon');
    }

    public function test_checkout_adds_fixed_shipping_fee_and_formats_delivery_details(): void
    {
        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category' => 'Bread',
            'product_name' => 'Classic Sourdough',
            'description' => 'Naturally leavened loaf.',
            'price' => 500,
            'price_label' => 'PHP 500',
            'is_active' => true,
        ]);

        $addResponse = $this
            ->actingAs($customer)
            ->postJson("/api/cart/add/{$product->id}", [
                'quantity' => 1,
            ]);

        $addResponse->assertOk();
        $cartItemId = $addResponse->json('data.item.id');

        $capturedPendingOrder = null;

        $this->mock(PayMongoService::class, function (MockInterface $mock) use (&$capturedPendingOrder): void {
            $mock->shouldReceive('createCheckoutSessionFromPendingOrder')
                ->once()
                ->withArgs(function (array $pendingOrder) use (&$capturedPendingOrder): bool {
                    $capturedPendingOrder = $pendingOrder;

                    return true;
                })
                ->andReturn([
                    'id' => 'cs_test_checkout_shipping',
                    'attributes' => [
                        'checkout_url' => 'https://paymongo.test/checkout/shipping',
                        'reference_number' => 'BD-PND-SHIPPING',
                        'status' => 'active',
                    ],
                ]);
        });

        $response = $this
            ->actingAs($customer)
            ->postJson('/api/checkout', [
                'item_ids' => [$cartItemId],
                'payment_method' => 'gcash',
                'fulfillment_method' => 'delivery',
                'shipping_address' => '123 Baker Street, Pasig City',
                'shipping_pin_link' => 'https://maps.example/pin',
            ]);

        $response
            ->assertOk()
            ->assertJsonPath('data.order.total_amount', 650)
            ->assertJsonPath('data.order.shipping_address', '123 Baker Street, Pasig City | Pin link: https://maps.example/pin')
            ->assertJsonPath('data.order.checkout_url', 'https://paymongo.test/checkout/shipping');

        $this->assertNotNull($capturedPendingOrder);
        $this->assertSame(650.0, $capturedPendingOrder['total_amount']);
        $this->assertSame('123 Baker Street, Pasig City | Pin link: https://maps.example/pin', $capturedPendingOrder['shipping_address']);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_checkout_pickup_uses_shop_details_and_skips_shipping_fee(): void
    {
        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category' => 'Bread',
            'product_name' => 'Classic Sourdough',
            'description' => 'Naturally leavened loaf.',
            'price' => 500,
            'price_label' => 'PHP 500',
            'is_active' => true,
        ]);

        $addResponse = $this
            ->actingAs($customer)
            ->postJson("/api/cart/add/{$product->id}", [
                'quantity' => 1,
            ]);

        $addResponse->assertOk();
        $cartItemId = $addResponse->json('data.item.id');

        $capturedPendingOrder = null;

        $this->mock(PayMongoService::class, function (MockInterface $mock) use (&$capturedPendingOrder): void {
            $mock->shouldReceive('createCheckoutSessionFromPendingOrder')
                ->once()
                ->withArgs(function (array $pendingOrder) use (&$capturedPendingOrder): bool {
                    $capturedPendingOrder = $pendingOrder;

                    return true;
                })
                ->andReturn([
                    'id' => 'cs_test_checkout_pickup',
                    'attributes' => [
                        'checkout_url' => 'https://paymongo.test/checkout/pickup',
                        'reference_number' => 'BD-PND-PICKUP',
                        'status' => 'active',
                    ],
                ]);
        });

        $response = $this
            ->actingAs($customer)
            ->postJson('/api/checkout', [
                'item_ids' => [$cartItemId],
                'payment_method' => 'gcash',
                'fulfillment_method' => 'pickup',
            ]);

        $pickupAddress = 'Pickup at Bakerdan Shop | Bakerdan Shop, 28 Market Avenue, San Nicolas, Pasig City, Metro Manila | Pin link: https://maps.app.goo.gl/bakerdan-pickup-placeholder';

        $response
            ->assertOk()
            ->assertJsonPath('data.order.total_amount', 500)
            ->assertJsonPath('data.order.shipping_address', $pickupAddress)
            ->assertJsonPath('data.order.checkout_url', 'https://paymongo.test/checkout/pickup');

        $this->assertNotNull($capturedPendingOrder);
        $this->assertSame(500.0, $capturedPendingOrder['total_amount']);
        $this->assertSame($pickupAddress, $capturedPendingOrder['shipping_address']);
        $this->assertDatabaseCount('cart_items', 0);
    }

    public function test_orders_index_materializes_paid_pending_order_on_payment_return(): void
    {
        $this->withoutMiddleware();

        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category' => 'Bread',
            'product_name' => 'Classic Sourdough',
            'description' => 'Naturally leavened loaf.',
            'price' => 500,
            'price_label' => 'PHP 500',
            'is_active' => true,
        ]);

        $addResponse = $this
            ->actingAs($customer)
            ->postJson("/api/cart/add/{$product->id}", [
                'quantity' => 1,
            ]);

        $addResponse->assertOk();

        $session = [
            'id' => 'cs_test_paid_return',
            'attributes' => [
                'checkout_url' => 'https://paymongo.test/checkout/paid-return',
                'reference_number' => 'BD-PND-PAID',
                'status' => 'paid',
                'payment_intent' => [
                    'id' => 'pi_test_paid_return',
                    'attributes' => [
                        'status' => 'succeeded',
                    ],
                ],
                'payments' => [
                    [
                        'id' => 'pay_test_paid_return',
                        'attributes' => [
                            'paid_at' => now()->timestamp,
                            'status' => 'paid',
                        ],
                    ],
                ],
            ],
        ];

        $this->mock(PayMongoService::class, function (MockInterface $mock) use ($session): void {
            $mock->shouldReceive('createCheckoutSessionFromPendingOrder')
                ->once()
                ->andReturn($session);
            $mock->shouldReceive('retrieveCheckoutSession')
                ->once()
                ->with('cs_test_paid_return', 6)
                ->andReturn($session);
            $mock->shouldReceive('checkoutSessionPaymentStatus')
                ->once()
                ->with($session)
                ->andReturn('paid');
            $mock->shouldReceive('syncOrderPayment')
                ->once()
                ->andReturnUsing(function (Order $order, array $checkoutSession): Order {
                    $order->forceFill([
                        'payment_status' => 'paid',
                        'payment_paid_at' => now(),
                        'payment_reference' => data_get($checkoutSession, 'attributes.reference_number'),
                        'payment_session_id' => $checkoutSession['id'],
                        'payment_checkout_url' => data_get($checkoutSession, 'attributes.checkout_url'),
                    ])->save();

                    return $order->fresh(['items.product']);
                });
        });

        $checkoutResponse = $this
            ->actingAs($customer)
            ->postJson('/api/checkout', [
                'item_ids' => [$addResponse->json('data.item.id')],
                'payment_method' => 'gcash',
                'fulfillment_method' => 'pickup',
            ]);

        $checkoutResponse->assertOk();
        $pendingOrderId = $checkoutResponse->json('data.order.id');

        $this
            ->actingAs($customer)
            ->getJson("/api/orders?highlight={$pendingOrderId}&payment_return=success")
            ->assertOk()
            ->assertJsonPath('data.highlight.materialized', true)
            ->assertJsonPath('data.highlight.replaced_pending_order_id', $pendingOrderId)
            ->assertJsonPath('data.orders.0.payment_status', 'paid')
            ->assertJsonPath('data.orders.0.is_pending_checkout', null);

        $this->assertDatabaseHas('orders', [
            'user_id' => $customer->user_id,
            'payment_status' => 'paid',
            'payment_session_id' => 'cs_test_paid_return',
        ]);
    }
}
