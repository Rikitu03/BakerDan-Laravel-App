<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminInventoryWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_removing_product_with_order_history_archives_it_instead_of_deleting(): void
    {
        $admin = User::query()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'product_name' => 'Referenced Product',
            'category' => 'Bread',
            'description' => 'Used in a previous order',
            'price' => 120,
            'is_active' => true,
        ]);

        $order = Order::query()->create([
            'user_id' => $customer->user_id,
            'total_amount' => 120,
            'status' => 'pending',
            'payment_status' => 'unpaid',
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 120,
        ]);

        $response = $this->actingAs($admin)->deleteJson("/admin/inventory/{$product->id}");

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Product is linked to existing orders or carts, so it was archived instead of deleted.',
            ])
            ->assertJsonPath('data.product.id', $product->id)
            ->assertJsonPath('data.product.is_active', false)
            ->assertJsonPath('data.product.status', 'inactive');

        $this->assertDatabaseHas('inventory_products', [
            'id' => $product->id,
            'is_active' => false,
        ]);
    }

    public function test_admin_can_create_a_product_from_inventory_form(): void
    {
        $admin = User::query()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->postJson('/admin/inventory', [
            'product_name' => 'Chocolate Ensaymada',
            'description' => 'Soft bread with chocolate filling',
            'price' => '145.50',
            'category' => 'Bread',
            'is_active' => '1',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Product added successfully.',
            ])
            ->assertJsonPath('data.product.name', 'Chocolate Ensaymada')
            ->assertJsonPath('data.product.category', 'Bread')
            ->assertJsonPath('data.product.is_active', true);

        $this->assertDatabaseHas('inventory_products', [
            'product_name' => 'Chocolate Ensaymada',
            'category' => 'Bread',
            'is_active' => true,
        ]);
    }

    public function test_product_save_still_succeeds_when_customer_notifications_fail(): void
    {
        $admin = User::query()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        Schema::drop('customer_notifications');

        $response = $this->actingAs($admin)->postJson('/admin/inventory', [
            'product_name' => 'Resilient Product',
            'description' => 'Still saves when notifications are unavailable',
            'price' => '210',
            'category' => 'Pastries',
            'is_active' => '1',
        ]);

        $response
            ->assertOk()
            ->assertJson([
                'success' => true,
                'message' => 'Product added successfully.',
            ])
            ->assertJsonPath('data.product.name', 'Resilient Product');

        $this->assertDatabaseHas('inventory_products', [
            'product_name' => 'Resilient Product',
            'category' => 'Pastries',
        ]);
    }
}
