<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\CartItem;
use App\Models\User;
use Database\Seeders\ProductCatalogSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware();
    }

    public function test_product_catalog_seed_populates_inventory_and_serves_products_api(): void
    {
        $this->seed(ProductCatalogSeeder::class);

        $this->assertSame(35, Product::query()->count());

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

        $this->assertDatabaseHas('inventory_products', [
            'category' => 'Pastries',
            'product_name' => 'Eclairs',
            'image_url' => '/images/bakerdan/pastries/eclair.jpg',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('inventory_products', [
            'category' => 'Pastries',
            'product_name' => 'Golden Eggpies',
            'image_url' => '/images/bakerdan/pastries/golden_eggpies.jpg',
            'is_active' => true,
        ]);

        $this->assertDatabaseHas('inventory_products', [
            'category' => 'Bread',
            'product_name' => 'Corned Beef Pandesal',
            'image_url' => '/images/bakerdan/bread/cornbeef_pandesal.jpg',
            'is_active' => true,
        ]);

        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this
            ->actingAs($customer)
            ->getJson('/api/products')
            ->assertOk()
            ->assertHeader('Cache-Control', 'max-age=0, no-store, private')
            ->assertJsonPath('success', true)
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('pagination.total', 35)
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonPath('pagination.last_page', 4)
            ->assertJsonFragment([
                'product_name' => 'Brazo Roll',
                'image_url' => '/images/bakerdan/brazo and cakes/brazo-roll_classic.jpg',
            ]);

        $this
            ->actingAs($customer)
            ->getJson('/api/products?page=2&per_page=99')
            ->assertOk()
            ->assertJsonCount(10, 'data')
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonPath('pagination.current_page', 2);

        $this
            ->actingAs($customer)
            ->getJson('/api/products?category=Sugar%20Cookies')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'product_name' => 'Sugar Cookies',
                'image_url' => '/images/bakerdan/sugar cookies/sugar_cookies.jpg',
            ]);

        $this
            ->actingAs($customer)
            ->getJson('/api/products?category=Pastries&page=1&per_page=10&sort=popularity')
            ->assertOk()
            ->assertJsonPath('pagination.per_page', 10)
            ->assertJsonPath('pagination.current_page', 1)
            ->assertJsonFragment([
                'category' => 'Pastries',
            ]);
    }

    public function test_catalog_product_options_are_exposed_and_priced_in_cart(): void
    {
        $this->seed(ProductCatalogSeeder::class);

        $eclairs = Product::query()->where('product_name', 'Eclairs')->firstOrFail();

        $this
            ->getJson("/api/products/{$eclairs->id}")
            ->assertOk()
            ->assertJsonPath('data.options.2.id', 'mix')
            ->assertJsonPath('data.options.2.label', 'Mix Eclairs')
            ->assertJsonPath('data.options.2.price', 320)
            ->assertJsonPath('data.options.2.image_url', '/images/bakerdan/pastries/eclair.jpg');

        $glassDesserts = Product::query()->where('product_name', 'Glass Dessert Cups')->firstOrFail();

        $this
            ->getJson("/api/products/{$glassDesserts->id}")
            ->assertOk()
            ->assertJsonPath('data.options.0.id', 'mousse-mango')
            ->assertJsonPath('data.options.0.image_url', '/images/bakerdan/pastries/glass_dessert_cups_mousse_mango.jpg')
            ->assertJsonPath('data.options.2.id', 'mousse-cookies-cream')
            ->assertJsonPath('data.options.2.image_url', '/images/bakerdan/pastries/glass_dessert_cups_mousse_cookies&cream.jpg');

        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this
            ->actingAs($customer)
            ->postJson("/api/cart/add/{$eclairs->id}", [
                'quantity' => 2,
                'option_id' => 'mix',
            ])
            ->assertOk()
            ->assertJsonPath('data.item.price', 320)
            ->assertJsonPath('data.item.line_total', 640)
            ->assertJsonPath('data.item.size', 'Box of 12 pcs')
            ->assertJsonPath('data.item.flavor', 'Mix Eclairs');
    }

    public function test_catalog_search_includes_flavor_and_option_text(): void
    {
        $this->seed(ProductCatalogSeeder::class);

        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this
            ->actingAs($customer)
            ->getJson('/api/products?search=smores')
            ->assertOk()
            ->assertJsonFragment([
                'product_name' => 'Cookie Bites',
            ]);
    }

    public function test_catalog_option_validation_rejects_invalid_options_and_enforces_minimum_quantity(): void
    {
        $this->seed(ProductCatalogSeeder::class);

        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);

        $eclairs = Product::query()->where('product_name', 'Eclairs')->firstOrFail();
        $sugarCookies = Product::query()->where('product_name', 'Sugar Cookies')->firstOrFail();

        $this
            ->actingAs($customer)
            ->postJson("/api/cart/add/{$eclairs->id}", [
                'quantity' => 1,
                'option_id' => 'not-a-real-option',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'Please choose a valid option for this product.');

        $this
            ->actingAs($customer)
            ->postJson("/api/cart/add/{$sugarCookies->id}", [
                'quantity' => 1,
                'option_id' => 'edible-print',
            ])
            ->assertStatus(422)
            ->assertJsonPath('message', 'This option requires a minimum quantity of 25.');

        $this
            ->actingAs($customer)
            ->postJson("/api/cart/add/{$sugarCookies->id}", [
                'quantity' => 25,
                'option_id' => 'edible-print',
            ])
            ->assertOk()
            ->assertJsonPath('data.cart.item_count', 1)
            ->assertJsonPath('data.cart.quantity_count', 25)
            ->assertJsonPath('data.item.optionId', 'edible-print')
            ->assertJsonPath('data.item.minimumQuantity', 25)
            ->assertJsonPath('data.item.quantity', 25);
    }

    public function test_customer_cart_native_add_redirects_after_catalog_option_add(): void
    {
        $this->seed(ProductCatalogSeeder::class);

        $customer = User::query()->create([
            'role' => 'customer',
            'is_active' => true,
        ]);
        $brazoCups = Product::query()->where('product_name', 'Brazo Cups')->firstOrFail();

        $response = $this
            ->actingAs($customer)
            ->from("/customer/cart?preview={$brazoCups->id}&option=classic")
            ->post("/customer/cart/add/{$brazoCups->id}", [
                'quantity' => 1,
                'option_id' => 'classic',
                'return_to' => "/customer/cart?preview={$brazoCups->id}&option=classic",
            ]);

        $cartItem = CartItem::query()->firstOrFail();

        $response->assertRedirect("/customer/cart?added={$cartItem->id}");
        $this->assertSame($brazoCups->id, $cartItem->product_id);
        $this->assertSame(1, $cartItem->quantity);
        $this->assertSame('Box of 24 pcs', $cartItem->size);
        $this->assertSame('Classic', $cartItem->flavor);
    }
}
