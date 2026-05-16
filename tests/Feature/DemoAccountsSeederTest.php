<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserDetail;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoAccountsSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_keeps_demo_customer_and_admin_accounts_available(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $customerDetail = UserDetail::query()->where('username', 'demo_customer')->firstOrFail();
        $adminDetail = UserDetail::query()->where('username', 'demo_admin')->firstOrFail();

        $this->assertSame('customer@bakerdan.test', $customerDetail->email);
        $this->assertSame('admin@bakerdan.test', $adminDetail->email);
        $this->assertTrue(Hash::check('password123', $customerDetail->password));
        $this->assertTrue(Hash::check('password123', $adminDetail->password));

        $this->assertDatabaseHas('users', [
            'user_id' => $customerDetail->user_id,
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('users', [
            'user_id' => $adminDetail->user_id,
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->assertSame(1, UserDetail::query()->where('username', 'demo_customer')->count());
        $this->assertSame(1, UserDetail::query()->where('username', 'demo_admin')->count());
        $this->assertSame(2, User::query()->whereIn('user_id', [$customerDetail->user_id, $adminDetail->user_id], 'and', false)->count());
    }
}
