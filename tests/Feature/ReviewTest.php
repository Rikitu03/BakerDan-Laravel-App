<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Review;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReviewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);
    }

    public function test_customer_can_submit_review_for_delivered_order()
    {
        $user = User::create(['role' => 'customer']);

        $product = Product::create([
            'product_name' => 'Test Product',
            'price' => 100,
            'is_active' => true,
            'category' => 'Cakes'
        ]);

        $order = Order::create([
            'user_id' => $user->user_id,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'total_amount' => 100,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100,
            'item_type' => 'catalog',
            'product_name' => 'Test Product',
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/reviews', [
                'order_id' => $order->id,
                'product_id' => $product->id,
                'rating' => 5,
                'comment' => 'Delicious!',
            ]);

        $response->assertStatus(201)
            ->assertJsonPath('message', 'Review submitted successfully.');

        $this->assertDatabaseHas('reviews', [
            'user_id' => $user->user_id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 5,
            'comment' => 'Delicious!',
        ]);
    }

    public function test_customer_cannot_review_non_delivered_order()
    {
        $user = User::create(['role' => 'customer']);

        $product = Product::create([
            'product_name' => 'Test Product',
            'price' => 100,
            'is_active' => true,
            'category' => 'Cakes'
        ]);

        $order = Order::create([
            'user_id' => $user->user_id,
            'status' => 'pending',
            'total_amount' => 100,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100,
            'item_type' => 'catalog',
            'product_name' => 'Test Product',
        ]);

        $response = $this->actingAs($user)
            ->postJson('/api/reviews', [
                'order_id' => $order->id,
                'product_id' => $product->id,
                'rating' => 5,
            ]);

        $response->assertStatus(403);
    }

    public function test_product_api_includes_rating_data()
    {
        $user = User::create(['role' => 'customer']);
        $product = Product::create([
            'product_name' => 'Test Product',
            'price' => 100,
            'is_active' => true,
            'category' => 'Cakes'
        ]);

        $order = Order::create([
            'user_id' => $user->user_id,
            'status' => 'delivered',
            'total_amount' => 100,
        ]);

        Review::create([
            'user_id' => $user->user_id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 4,
            'comment' => 'Good',
        ]);

        $response = $this->getJson('/api/products/' . $product->id);

        $response->assertStatus(200)
            ->assertJsonPath('data.average_rating', 4)
            ->assertJsonPath('data.review_count', 1);
    }

    public function test_purchase_history_includes_review_status()
    {
        $user = User::create(['role' => 'customer']);
        $product = Product::create([
            'product_name' => 'Test Product',
            'price' => 100,
            'is_active' => true,
            'category' => 'Cakes'
        ]);

        $order = Order::create([
            'user_id' => $user->user_id,
            'status' => 'delivered',
            'payment_status' => 'paid',
            'total_amount' => 100,
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => 100,
            'item_type' => 'catalog',
            'product_name' => 'Test Product',
        ]);

        Review::create([
            'user_id' => $user->user_id,
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => 5,
        ]);

        $response = $this->actingAs($user)
            ->getJson('/api/purchases');

        $response->assertStatus(200)
            ->assertJsonPath('data.purchases.0.items.0.is_reviewed', true);
    }
}
