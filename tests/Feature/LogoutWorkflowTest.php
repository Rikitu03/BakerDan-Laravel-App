<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogoutWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_logout_invalidates_session_redirects_to_login_and_disables_browser_cache(): void
    {
        $user = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->post('/logout');

        $response
            ->assertRedirect('/login')
            ->assertHeader('Pragma', 'no-cache');

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control', ''));
        $this->assertStringContainsString('no-cache', $response->headers->get('Cache-Control', ''));

        $this->assertGuest();
    }

    public function test_customer_spa_response_is_not_browser_cacheable(): void
    {
        $user = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->get('/customer/orders');

        $response
            ->assertOk()
            ->assertViewIs('customer.app')
            ->assertHeader('Pragma', 'no-cache');

        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control', ''));
        $this->assertStringContainsString('no-cache', $response->headers->get('Cache-Control', ''));
    }
}
