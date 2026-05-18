<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promo;
use App\Models\User;
use App\Services\PendingOrderService;
use App\Services\PayMongoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Mockery\MockInterface;
use Tests\TestCase;

class PromoWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'ProductCatalogSeeder']);
    }

    public function test_customer_can_validate_active_promo_code()
    {
        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);
        $product = Product::first();

        // Create cart for user
        $cart = Cart::create(['user_id' => $customer->user_id]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => $product->price,
        ]);

        // Create active promo
        $promo = Promo::create([
            'code' => 'SAVE10',
            'discount_type' => 'percentage',
            'discount_value' => 10.00,
            'is_active' => true,
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
        ]);

        $response = $this->actingAs($customer)
            ->postJson('/api/promos/validate', [
                'code' => 'save10',
                'item_ids' => [$cartItem->id],
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'code' => 'SAVE10',
            ]);

        $discountAmount = round(($product->price * 2 * 10) / 100, 2);
        $this->assertEquals($discountAmount, $response->json('discount_amount'));
    }

    public function test_promo_code_respects_minimum_purchase()
    {
        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);
        $product = Product::first();

        $cart = Cart::create(['user_id' => $customer->user_id]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 1,
            'price' => $product->price,
        ]);

        $promo = Promo::create([
            'code' => 'SAVE100',
            'discount_type' => 'fixed',
            'discount_value' => 100.00,
            'min_purchase' => 5000.00,
            'is_active' => true,
        ]);

        $response = $this->actingAs($customer)
            ->postJson('/api/promos/validate', [
                'code' => 'SAVE100',
                'item_ids' => [$cartItem->id],
            ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Minimum purchase of PHP 5,000.00 is required for applicable products.',
            ]);
    }

    public function test_checkout_applies_promo_discount_to_total()
    {
        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);
        $product = Product::first();

        $cart = Cart::create(['user_id' => $customer->user_id]);
        $cartItem = CartItem::create([
            'cart_id' => $cart->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => $product->price,
        ]);

        $promo = Promo::create([
            'code' => 'DISCOUNT50',
            'discount_type' => 'fixed',
            'discount_value' => 50.00,
            'is_active' => true,
        ]);

        $session = [
            'id' => 'cs_test_promo',
            'attributes' => [
                'checkout_url' => 'https://paymongo.test/checkout/promo',
                'reference_number' => 'BD-PND-PROMO',
                'status' => 'active',
            ],
        ];

        $this->mock(PayMongoService::class, function (MockInterface $mock) use ($session): void {
            $mock->shouldReceive('createCheckoutSessionFromPendingOrder')
                ->once()
                ->andReturn($session);
        });

        $response = $this->actingAs($customer)
            ->postJson('/api/checkout', [
                'item_ids' => [$cartItem->id],
                'payment_method' => 'gcash',
                'fulfillment_method' => 'pickup',
                'promo_code' => 'DISCOUNT50',
            ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ]);

        $pendingOrderId = $response->json('data.order.id');
        $this->assertNotNull($pendingOrderId);

        $pendingOrderService = app(PendingOrderService::class);
        $pendingOrder = $pendingOrderService->findForUser($customer->user_id, $pendingOrderId);

        $expectedTotal = ($product->price * 2) - 50.00;
        $this->assertEquals(round($expectedTotal, 2), $pendingOrder['total_amount']);
        $this->assertEquals($promo->id, $pendingOrder['promo_id']);
        $this->assertEquals(50.00, $pendingOrder['discount_amount']);
    }
}
