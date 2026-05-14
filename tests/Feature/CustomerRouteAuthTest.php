<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerRouteAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cart_shortcut_redirects_to_login(): void
    {
        $this
            ->get('/cart')
            ->assertRedirect('/login');
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

    public function test_guest_customer_cart_route_redirects_to_login(): void
    {
        $this
            ->get('/customer/cart')
            ->assertRedirect('/login');
    }
}
