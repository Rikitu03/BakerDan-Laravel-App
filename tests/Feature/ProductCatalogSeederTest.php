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

        $this->assertSame(29, Product::query()->count());

        $this->assertDatabaseHas('inventory_products', [
            'category' => 'Bread',
            'product_name' => 'Creme Cheese Garlic (Box of 6 pcs)',
            'image_url' => '/images/bakerdan/Creme_Cheese_Garlic.png',
        ]);

        $this->assertDatabaseHas('inventory_products', [
            'category' => 'Bread',
            'product_name' => 'Ube / Classic Potato Ensaymada',
            'image_url' => '/images/bakerdan/Bread.png',
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
            ->assertJsonCount(29, 'data')
            ->assertJsonFragment([
                'product_name' => 'Piped Cupcakes (12 pcs)',
                'image_url' => '/images/bakerdan/piped_cupcakes.jpg',
            ]);
    }
}
