<?php

namespace Tests\Feature;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerRouteAuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);
    }

    public function test_guest_cart_shortcut_redirects_to_customer_cart(): void
    {
        $this
            ->get('/cart')
            ->assertRedirect('/customer/cart');
    }

    public function test_customer_cart_shortcut_redirects_to_customer_cart(): void
    {
        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this
            ->actingAs($customer)
            ->get('/cart')
            ->assertRedirect('/customer/cart');
    }

    public function test_guest_customer_cart_route_serves_customer_spa(): void
    {
        $this
            ->get('/customer/cart')
            ->assertOk()
            ->assertSee('window.Laravel', false)
            ->assertSee('check: false', false);
    }

    public function test_customer_profile_endpoint_returns_personal_info_from_user_details(): void
    {
        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        UserDetail::query()->create([
            'user_id' => $customer->user_id,
            'name' => 'Database Detail Customer',
            'username' => 'dbdetailcustomer',
            'age' => 31,
            'email' => 'dbdetail@example.com',
            'contact' => '+63 917 111 2222',
            'address' => '45 Real Street, Pasig City',
            'password' => Hash::make('password123'),
        ]);

        $this
            ->actingAs($customer)
            ->getJson('/api/profile')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.name', 'Database Detail Customer')
            ->assertJsonPath('data.email', 'dbdetail@example.com')
            ->assertJsonPath('data.phone', '+63 917 111 2222')
            ->assertJsonPath('data.address', '45 Real Street, Pasig City');
    }

    public function test_customer_spa_bootstraps_personal_info_from_user_details(): void
    {
        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        UserDetail::query()->create([
            'user_id' => $customer->user_id,
            'name' => 'Boot Detail Customer',
            'username' => 'bootdetailcustomer',
            'age' => 29,
            'email' => 'bootdetail@example.com',
            'contact' => '+63 918 333 4444',
            'address' => '88 Bootstrap Avenue, Pasig City',
            'password' => Hash::make('password123'),
        ]);

        $this
            ->actingAs($customer)
            ->get('/customer/settings')
            ->assertOk()
            ->assertSee('"name":"Boot Detail Customer"', false)
            ->assertSee('"email":"bootdetail@example.com"', false)
            ->assertSee('"phone":"+63 918 333 4444"', false)
            ->assertSee('"address":"88 Bootstrap Avenue, Pasig City"', false);
    }

    public function test_guest_cart_merges_into_customer_cart_on_login(): void
    {
        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        UserDetail::query()->create([
            'user_id' => $customer->user_id,
            'name' => 'Cart Customer',
            'username' => 'cartcustomer',
            'age' => 28,
            'email' => 'cart@example.com',
            'contact' => '+63 999 123 4567',
            'address' => 'Pasig City',
            'password' => Hash::make('password123'),
        ]);

        $existingProduct = Product::query()->create([
            'category' => 'Bread',
            'product_name' => 'Existing Loaf',
            'description' => 'Already in the account cart.',
            'price' => 120,
            'price_label' => 'PHP 120',
            'is_active' => true,
        ]);

        $guestProduct = Product::query()->create([
            'category' => 'Pastries',
            'product_name' => 'Guest Tart',
            'description' => 'Added before login.',
            'price' => 80,
            'price_label' => 'PHP 80',
            'is_active' => true,
        ]);

        $cart = Cart::query()->create(['user_id' => $customer->user_id]);
        $existingItem = CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_id' => $existingProduct->id,
            'item_type' => 'catalog',
            'quantity' => 1,
            'unit_price' => 120,
            'product_name' => 'Existing Loaf',
            'description' => 'Already in the account cart.',
        ]);

        $guestAddResponse = $this
            ->postJson("/api/cart/add/{$guestProduct->id}", [
                'quantity' => 2,
            ])
            ->assertOk()
            ->assertJsonPath('data.item.name', 'Guest Tart');

        $guestItemId = $guestAddResponse->json('data.item.id');

        $loginResponse = $this
            ->post('/login', [
                'username' => 'cartcustomer',
                'password' => 'password123',
                'redirect' => "/customer/checkout?items={$guestItemId}",
            ])
            ->assertRedirect()
            ->assertSessionHas('auth.password_hash_user_id', $customer->user_id)
            ->assertSessionHas('auth.password_hash');

        $this->assertAuthenticatedAs($customer);

        $mergedItem = CartItem::query()
            ->whereHas('cart', fn ($query) => $query->where('user_id', $customer->user_id))
            ->where('product_id', $guestProduct->id)
            ->first();

        $this->assertNotNull($mergedItem);
        $this->assertSame(2, (int) $mergedItem->quantity);

        $redirectTarget = $loginResponse->headers->get('Location');
        $this->assertNotNull($redirectTarget);

        $parts = parse_url((string) $redirectTarget);
        parse_str($parts['query'] ?? '', $query);
        $redirectItemIds = collect(explode(',', (string) ($query['items'] ?? '')))
            ->map(fn (string $id): int => (int) $id)
            ->filter()
            ->values()
            ->all();

        $this->assertSame('/customer/checkout', $parts['path'] ?? null);
        $this->assertContains((int) $existingItem->id, $redirectItemIds);
        $this->assertContains((int) $mergedItem->id, $redirectItemIds);
    }
}
