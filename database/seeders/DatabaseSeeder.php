<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    private const DEMO_PASSWORD = 'password123';

    /**
     * These demo accounts are intentional and should stay available for local demos.
     *
     * @var list<array<string, string>>
     */
    private const DEMO_ACCOUNTS = [
        [
            'role' => 'customer',
            'name' => 'Demo Customer',
            'username' => 'demo_customer',
            'email' => 'customer@bakerdan.test',
            'contact' => '09123456789',
            'address' => 'Bakerdan Demo Customer Address',
        ],
        [
            'role' => 'admin',
            'name' => 'Demo Admin',
            'username' => 'demo_admin',
            'email' => 'admin@bakerdan.test',
            'contact' => '09987654321',
            'address' => 'Bakerdan Demo Admin Address',
        ],
    ];

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ProductCatalogSeeder::class);
        $this->call(PromoSeeder::class);

        foreach (self::DEMO_ACCOUNTS as $account) {
            $this->createDemoAccount(...$account);
        }
    }

    private function createDemoAccount(
        string $role,
        string $name,
        string $username,
        string $email,
        string $contact,
        string $address,
    ): void {
        $existingDetail = UserDetail::query()
            ->where('email', $email)
            ->orWhere('username', $username)
            ->first();

        $user = $existingDetail?->user;

        if (!$user) {
            $user = User::query()->create([
                'role' => $role,
                'is_active' => true,
            ]);
        } else {
            $user->update([
                'role' => $role,
                'is_active' => true,
            ]);
        }

        UserDetail::query()->updateOrCreate(
            ['user_id' => $user->user_id],
            [
                'name' => $name,
                'username' => $username,
                'age' => 25,
                'email' => $email,
                'contact' => $contact,
                'address' => $address,
                'password' => self::DEMO_PASSWORD,
            ],
        );
    }
}
