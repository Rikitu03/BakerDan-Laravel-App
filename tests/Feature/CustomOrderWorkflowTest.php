<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Tests\TestCase;

class CustomOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(VerifyCsrfToken::class);
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
}
