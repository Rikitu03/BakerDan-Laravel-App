<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(ProductCatalogSeeder::class);

        $existingDetail = UserDetail::query()->where('email', 'test@example.com')->first();
        $user = $existingDetail?->user;

        if (!$user) {
            $user = User::query()->create([
                'role' => 'customer',
                'is_active' => true,
            ]);
        }

        UserDetail::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'username' => 'testuser',
                'age' => 25,
                'user_id' => $user->user_id,
                'contact' => '09123456789',
                'address' => 'Bakerdan Demo Address',
                'password' => 'password123',
            ],
        );
    }
}
