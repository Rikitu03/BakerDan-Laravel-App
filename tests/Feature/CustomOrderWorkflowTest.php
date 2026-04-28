<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomOrderWorkflowTest extends TestCase
{
    use RefreshDatabase;

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
}
