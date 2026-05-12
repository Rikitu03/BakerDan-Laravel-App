<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_catalog_seed_populates_inventory_and_serves_products_api(): void
    {
        $this->seed(ProductCatalogSeeder::class);

        $this->assertSame(48, Product::query()->count());

        $this->assertDatabaseHas('inventory_products', [
            'category' => 'Bread',
            'product_name' => 'Korean Garlic Creamcheese Bun (KBUN)',
            'image_url' => '/images/bakerdan/bread/Creme_Cheese_Garlic.png',
        ]);

        $this->assertDatabaseHas('inventory_products', [
            'category' => 'Pastries',
            'product_name' => 'Coconut Macaroons',
            'image_url' => '/images/bakerdan/pastries/coconut_macaroons.jpg',
        ]);

        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this
            ->actingAs($customer)
            ->getJson('/api/products')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(48, 'data')
            ->assertJsonFragment([
                'product_name' => 'Brazo Roll - Tsokolate',
                'image_url' => '/images/bakerdan/brazo and cakes/brazo-roll_chocolate.jpg',
            ]);
    }
}
