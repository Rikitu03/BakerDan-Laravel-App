<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\Promo;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class PromoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. WELCOME10: 10% off storewide for new customers
        Promo::query()->updateOrCreate(
            ['code' => 'WELCOME10'],
            [
                'description' => '10% discount on your first order over PHP 500.00!',
                'discount_type' => 'percentage',
                'discount_value' => 10.00,
                'min_purchase' => 500.00,
                'max_discount' => 200.00,
                'usage_limit' => 1000,
                'limit_per_user' => 1,
                'usage_count' => 0,
                'starts_at' => Carbon::now()->subDays(5),
                'expires_at' => Carbon::now()->addYear(),
                'applicable_products' => null,
                'is_active' => true,
            ]
        );

        // 2. BAKERDAN150: Flat PHP 150 off on bulk orders
        Promo::query()->updateOrCreate(
            ['code' => 'BAKERDAN150'],
            [
                'description' => 'Flat PHP 150.00 off on delicious baked treats over PHP 1,000.00!',
                'discount_type' => 'fixed',
                'discount_value' => 150.00,
                'min_purchase' => 1000.00,
                'max_discount' => null,
                'usage_limit' => 500,
                'limit_per_user' => 3,
                'usage_count' => 0,
                'starts_at' => Carbon::now()->subDays(2),
                'expires_at' => Carbon::now()->addYear(),
                'applicable_products' => null,
                'is_active' => true,
            ]
        );

        // 3. SOURDOUGH20: 20% off Sourdough breads (Product specific)
        $sourdoughIds = Product::query()
            ->where('product_name', 'like', '%Sourdough%')
            ->pluck('id')
            ->toArray();

        Promo::query()->updateOrCreate(
            ['code' => 'SOURDOUGH20'],
            [
                'description' => 'Craving naturally leavened bread? Enjoy 20% off Sourdough items!',
                'discount_type' => 'percentage',
                'discount_value' => 20.00,
                'min_purchase' => 0.00,
                'max_discount' => 150.00,
                'usage_limit' => 200,
                'limit_per_user' => 2,
                'usage_count' => 0,
                'starts_at' => Carbon::now()->subDays(10),
                'expires_at' => Carbon::now()->addMonths(6),
                'applicable_products' => !empty($sourdoughIds) ? $sourdoughIds : null,
                'is_active' => true,
            ]
        );

        // 4. CAKEDELIGHT: PHP 300 off cakes (Category specific)
        $cakeIds = Product::query()
            ->where('category', 'Cakes')
            ->pluck('id')
            ->toArray();

        Promo::query()->updateOrCreate(
            ['code' => 'CAKEDELIGHT'],
            [
                'description' => 'Celebrate your special moments! PHP 300.00 discount on select celebration cakes.',
                'discount_type' => 'fixed',
                'discount_value' => 300.00,
                'min_purchase' => 1200.00,
                'max_discount' => null,
                'usage_limit' => 100,
                'limit_per_user' => 1,
                'usage_count' => 0,
                'starts_at' => Carbon::now()->subDays(1),
                'expires_at' => Carbon::now()->addMonths(3),
                'applicable_products' => !empty($cakeIds) ? $cakeIds : null,
                'is_active' => true,
            ]
        );

        // 5. FLASH50: Expired code for checking past promos UI
        Promo::query()->updateOrCreate(
            ['code' => 'FLASH50'],
            [
                'description' => 'Special mid-week flash sale of 50% discount (Expired).',
                'discount_type' => 'percentage',
                'discount_value' => 50.00,
                'min_purchase' => 0.00,
                'max_discount' => 500.00,
                'usage_limit' => 50,
                'limit_per_user' => 1,
                'usage_count' => 50,
                'starts_at' => Carbon::now()->subDays(15),
                'expires_at' => Carbon::now()->subDays(1),
                'applicable_products' => null,
                'is_active' => true,
            ]
        );

        // 6. HOLIDAY2026: Scheduled future promo code
        Promo::query()->updateOrCreate(
            ['code' => 'HOLIDAY2026'],
            [
                'description' => 'Preview our upcoming holiday treat catalog with 15% early discount!',
                'discount_type' => 'percentage',
                'discount_value' => 15.00,
                'min_purchase' => 800.00,
                'max_discount' => 300.00,
                'usage_limit' => 300,
                'limit_per_user' => 1,
                'usage_count' => 0,
                'starts_at' => Carbon::now()->addMonth(),
                'expires_at' => Carbon::now()->addMonths(2),
                'applicable_products' => null,
                'is_active' => true,
            ]
        );
    }
}
