<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductCatalogSeeder extends Seeder
{
    private const CATALOG_CATEGORIES = [
        'Bread',
        'Pastries',
        'Tarts',
        'Brazos and Cakes',
        'Sugar Cookies',
    ];

    public function run(): void
    {
        $products = array_merge(
            $this->sugarCookieProducts(),
            $this->breadProducts(),
            $this->pastryProducts(),
            $this->tartProducts(),
            $this->brazosAndCakesProducts(),
        );

        $currentKeys = array_map(
            fn (array $product): string => $this->productKey($product['category'], $product['product_name']),
            $products,
        );

        Product::query()
            ->whereIn('category', self::CATALOG_CATEGORIES)
            ->get()
            ->each(function (Product $product) use ($currentKeys): void {
                if (! in_array($this->productKey($product->category, $product->product_name), $currentKeys, true)) {
                    $product->update(['is_active' => false]);
                }
            });

        foreach ($products as $product) {
            Product::query()->updateOrCreate(
                ['product_name' => $product['product_name'], 'category' => $product['category']],
                $product,
            );
        }
    }

    private function sugarCookieProducts(): array
    {
        return [
            $this->product(
                category: 'Sugar Cookies',
                productName: 'Sugar Cookies',
                description: 'Minimum order of 25 pcs. Size 3-3.5 inches. Includes individual packaging, ribbons, and optional thank-you tags.',
                priceLabel: 'PHP 65.00 - 85.00 each',
                price: 65.00,
                sizesAvailable: '3-3.5 inches | Minimum order: 25 pcs',
                flavorsAvailable: 'Plain or Marble Fondant Design - PHP 65 each | Customized Character - PHP 70 each | Edible Print - PHP 85 each',
                imageUrl: '/images/bakerdan/sugar cookies/sugar_cookies.jpg',
            ),
        ];
    }

    private function breadProducts(): array
    {
        return [
            $this->product(
                category: 'Bread',
                productName: 'Korean Garlic Creamcheese Bun (KBUN)',
                description: 'Classic bestseller Korean garlic creamcheese buns.',
                priceLabel: 'PHP 395.00 - 410.00',
                price: 395.00,
                sizesAvailable: 'Mini (45g) - Box of 12 pcs - PHP 410 | Regular (90g) - Box of 6 pcs - PHP 395 | Big (130g) - Box of 4 pcs - PHP 395',
                flavorsAvailable: 'Classic',
                imageUrl: '/images/bakerdan/bread/Creme_Cheese_Garlic.png',
            ),
            $this->product(
                category: 'Bread',
                productName: 'Cinnamon Rolls w/ Creamcheese',
                description: 'Soft cinnamon rolls topped with creamcheese.',
                priceLabel: 'PHP 395.00 - 405.00',
                price: 395.00,
                sizesAvailable: 'Mini - Box of 12 pcs - PHP 405 | Big - Box of 6 pcs - PHP 395',
                flavorsAvailable: null,
                imageUrl: '/images/bakerdan/pastries/cinnamon_rolls_with_creamcheese.jpg',
            ),
            $this->product(
                category: 'Bread',
                productName: 'Filipino Ensaymada',
                description: 'Classic, Bacon, or Ube Filipino ensaymada.',
                priceLabel: 'PHP 275.00 - 295.00',
                price: 275.00,
                sizesAvailable: 'Mini (45g) - Box of 12 pcs - PHP 295 | Regular (90g) - Box of 6 pcs - PHP 275',
                flavorsAvailable: 'Classic | Bacon | Ube',
                imageUrl: '/images/bakerdan/bread/filipino_ensaymada.jpg',
            ),
            $this->product(
                category: 'Bread',
                productName: 'Ube Cheese Pandesal',
                description: 'Ube cheese pandesal, box of 10 pcs.',
                priceLabel: 'PHP 275.00',
                price: 275.00,
                sizesAvailable: 'Box of 10 pcs',
                flavorsAvailable: 'Ube Cheese',
                imageUrl: '/images/bakerdan/bread/ube_pandesal.jpg',
            ),
            $this->product(
                category: 'Bread',
                productName: 'Corned Beef Pandesal',
                description: 'Corned beef pandesal made with Highlands corned beef.',
                priceLabel: 'PHP 350.00',
                price: 350.00,
                sizesAvailable: 'Box of 6 pcs',
                flavorsAvailable: 'Corned Beef',
                imageUrl: '/images/bakerdan/bread/cornbeef_pandesal.jpg',
            ),
            $this->product(
                category: 'Bread',
                productName: 'Bread Loaves',
                description: 'Assorted flavored bread loaves.',
                priceLabel: 'PHP 200.00 - 250.00',
                price: 200.00,
                sizesAvailable: 'Loaf',
                flavorsAvailable: 'Choco Banana Almond - PHP 220 | Cheesy Garlic - PHP 200 | Pizza - PHP 200 | Carrot w/ Creamcheese - PHP 250',
                imageUrl: '/images/bakerdan/bread/bread_loaves.jpg',
            ),
        ];
    }

    private function pastryProducts(): array
    {
        return [
            $this->product(
                category: 'Pastries',
                productName: 'Creampuffs',
                description: 'Bestseller cream puffs, box of 12 pcs.',
                priceLabel: 'PHP 285.00 - 310.00',
                price: 285.00,
                sizesAvailable: 'Box of 12 pcs',
                flavorsAvailable: 'Caramel - PHP 285 | Strawberry - PHP 300 | Matcha - PHP 300 | Mix - PHP 310',
                imageUrl: '/images/bakerdan/pastries/Creme_Puffs.png',
            ),
            $this->product(
                category: 'Pastries',
                productName: 'Eclairs',
                description: 'Eclairs, box of 12 pcs.',
                priceLabel: 'PHP 310.00 - 320.00',
                price: 310.00,
                sizesAvailable: 'Box of 12 pcs',
                flavorsAvailable: 'Dark Chocolate - PHP 310 | Strawberry - PHP 310 | Mix Eclairs - PHP 320',
                imageUrl: '/images/bakerdan/pastries/eclair.jpg',
            ),
            $this->product(
                category: 'Pastries',
                productName: 'Muffins',
                description: 'Assorted muffins, box of 6 pcs.',
                priceLabel: 'PHP 340.00 - 420.00',
                price: 340.00,
                sizesAvailable: 'Box of 6 pcs',
                flavorsAvailable: 'Chocolate - PHP 350 | Blueberry - PHP 340 | Corn Muffin - PHP 340 | Pumpkin w/ Streusel - PHP 420',
                imageUrl: '/images/bakerdan/pastries/muffins.jpg',
            ),
            $this->product(
                category: 'Pastries',
                productName: 'Golden Eggpies',
                description: 'Golden egg pie, 8 inch round.',
                priceLabel: 'PHP 300.00',
                price: 300.00,
                sizesAvailable: '8 inch round',
                flavorsAvailable: null,
                imageUrl: '/images/bakerdan/pastries/golden_eggpies.jpg',
            ),
            $this->product(
                category: 'Pastries',
                productName: 'Coconut Macaroons',
                description: 'Coconut macaroons, box of 40 pcs.',
                priceLabel: 'PHP 330.00',
                price: 330.00,
                sizesAvailable: 'Box of 40 pcs',
                flavorsAvailable: null,
                imageUrl: '/images/bakerdan/pastries/coconut_macaroons.jpg',
            ),
            $this->product(
                category: 'Pastries',
                productName: 'Walnut Brownies',
                description: 'Bestseller walnut brownies, box of 15 pcs.',
                priceLabel: 'PHP 320.00',
                price: 320.00,
                sizesAvailable: 'Box of 15 pcs',
                flavorsAvailable: 'Walnut',
                imageUrl: '/images/bakerdan/pastries/Brownies.png',
            ),
            $this->product(
                category: 'Pastries',
                productName: 'Cookie Bites',
                description: '15g soft and chewy bite-sized cookies, box of 40 pcs.',
                priceLabel: 'PHP 460.00',
                price: 460.00,
                sizesAvailable: 'Box of 40 pcs | 15 grams each',
                flavorsAvailable: 'Oatmeal Cookies | Chocolate Chips | Red Velvet Smores | Matcha Smores | Chocolate Smores',
                imageUrl: '/images/bakerdan/pastries/cookie_bites.jpg',
            ),
            $this->product(
                category: 'Pastries',
                productName: 'Empanada',
                description: 'Bestseller empanada in regular and mini boxes.',
                priceLabel: 'PHP 420.00 - 460.00',
                price: 420.00,
                sizesAvailable: 'Regular - Box of 10 pcs | Mini - Box of 18 pcs',
                flavorsAvailable: 'Regular Tuna/Chicken - PHP 420 | Regular Pork - PHP 460 | Mini Tuna/Chicken - PHP 450',
                imageUrl: '/images/bakerdan/pastries/empanada.jpg',
            ),
            $this->product(
                category: 'Pastries',
                productName: 'Mini Donuts',
                description: 'Mini donuts, box of 12 pcs.',
                priceLabel: 'PHP 420.00 - 450.00',
                price: 420.00,
                sizesAvailable: 'Box of 12 pcs',
                flavorsAvailable: 'Creme Brulee - PHP 420 | Fruity w/ Custard - PHP 450',
                imageUrl: '/images/bakerdan/pastries/mini_donuts.jpg',
            ),
            $this->product(
                category: 'Pastries',
                productName: 'Petite Fours',
                description: 'Petite fours, 28 cake slices.',
                priceLabel: 'PHP 1,200.00',
                price: 1200.00,
                sizesAvailable: '28 cake slices',
                flavorsAvailable: null,
                imageUrl: '/images/bakerdan/pastries/petite_fours.jpg',
            ),
            $this->product(
                category: 'Pastries',
                productName: 'Glass Dessert Cups',
                description: 'Glass dessert cups, 24 pcs of 60ml desserts.',
                priceLabel: 'PHP 1,375.00 - 1,600.00',
                price: 1375.00,
                sizesAvailable: '24 pcs glass desserts | 60ml',
                flavorsAvailable: 'Mousse: Mango, Chocolate, Cookies & Cream, Strawberry - PHP 1,375; Biscoff - PHP 1,475 | Cheesecake: Mango, Strawberry, Blueberry, Oreo - PHP 1,475; Biscoff - PHP 1,600',
                imageUrl: '/images/bakerdan/pastries/glass_dessert_cups_mousse_biscoff.jpg',
            ),
        ];
    }

    private function tartProducts(): array
    {
        return [
            $this->product(
                category: 'Tarts',
                productName: 'Blueberry Tart',
                description: 'Blueberry tart, box of 12 pcs.',
                priceLabel: 'PHP 350.00',
                price: 350.00,
                sizesAvailable: 'Box of 12 pcs',
                flavorsAvailable: 'Blueberry',
                imageUrl: '/images/bakerdan/tarts/blueberry_tart.jpg',
            ),
            $this->product(
                category: 'Tarts',
                productName: 'Caramel Tart',
                description: 'Caramel tart, box of 12 pcs.',
                priceLabel: 'PHP 350.00',
                price: 350.00,
                sizesAvailable: 'Box of 12 pcs',
                flavorsAvailable: 'Caramel',
                imageUrl: '/images/bakerdan/tarts/caramel_tart.jpg',
            ),
            $this->product(
                category: 'Tarts',
                productName: 'Egg Tart',
                description: 'Egg tart, box of 12 pcs.',
                priceLabel: 'PHP 350.00',
                price: 350.00,
                sizesAvailable: 'Box of 12 pcs',
                flavorsAvailable: 'Egg',
                imageUrl: '/images/bakerdan/tarts/egg_tart.jpg',
            ),
            $this->product(
                category: 'Tarts',
                productName: 'Apple Crumble',
                description: 'Apple crumble tart, box of 12 pcs.',
                priceLabel: 'PHP 360.00',
                price: 360.00,
                sizesAvailable: 'Box of 12 pcs',
                flavorsAvailable: 'Apple',
                imageUrl: '/images/bakerdan/tarts/apple_tart.jpg',
            ),
            $this->product(
                category: 'Tarts',
                productName: 'Mango Flower Tart',
                description: 'Bestseller mango flower tart, box of 12 pcs.',
                priceLabel: 'PHP 370.00',
                price: 370.00,
                sizesAvailable: 'Box of 12 pcs',
                flavorsAvailable: 'Mango',
                imageUrl: '/images/bakerdan/tarts/mango_flower_tart.jpg',
            ),
            $this->product(
                category: 'Tarts',
                productName: 'Pineapple Tart',
                description: 'Pineapple tart, box of 12 pcs.',
                priceLabel: 'PHP 350.00',
                price: 350.00,
                sizesAvailable: 'Box of 12 pcs',
                flavorsAvailable: 'Pineapple',
                imageUrl: '/images/bakerdan/tarts/pineapple_tart.jpg',
            ),
            $this->product(
                category: 'Tarts',
                productName: 'Strawberry Tart',
                description: 'Strawberry tart, box of 12 pcs.',
                priceLabel: 'PHP 350.00',
                price: 350.00,
                sizesAvailable: 'Box of 12 pcs',
                flavorsAvailable: 'Strawberry',
                imageUrl: '/images/bakerdan/tarts/strawberry_tart.jpg',
            ),
            $this->product(
                category: 'Tarts',
                productName: 'Mix Fruit Tarts',
                description: 'Mix fruit tarts, box of 12 pcs.',
                priceLabel: 'PHP 350.00',
                price: 350.00,
                sizesAvailable: 'Box of 12 pcs',
                flavorsAvailable: 'Mix Fruit',
                imageUrl: '/images/bakerdan/tarts/mix_fruit_tart.jpg',
            ),
            $this->product(
                category: 'Tarts',
                productName: 'Assorted Fruit Tart',
                description: 'Bestseller assorted fruit tart, box of 12 pcs.',
                priceLabel: 'PHP 380.00',
                price: 380.00,
                sizesAvailable: 'Box of 12 pcs',
                flavorsAvailable: 'Blueberry | Strawberry | Mango | Mixfruit',
                imageUrl: '/images/bakerdan/tarts/assorted_fruit_tart.jpg',
            ),
        ];
    }

    private function brazosAndCakesProducts(): array
    {
        return [
            $this->product(
                category: 'Brazos and Cakes',
                productName: 'Brazo Cups',
                description: 'Bestseller brazo de Mercedes cups, box of 24 pcs.',
                priceLabel: 'PHP 590.00 - 620.00',
                price: 590.00,
                sizesAvailable: 'Box of 24 pcs',
                flavorsAvailable: 'Classic - PHP 590 | Mix - PHP 620',
                imageUrl: '/images/bakerdan/brazo and cakes/brazo_cups.jpg',
            ),
            $this->product(
                category: 'Brazos and Cakes',
                productName: 'Brazo Roll',
                description: 'Brazo de Mercedes roll.',
                priceLabel: 'PHP 500.00 - 530.00',
                price: 500.00,
                sizesAvailable: 'Roll',
                flavorsAvailable: 'Classic - PHP 500 | Ube - PHP 520 | Tsokolate - PHP 530 | Mocha - PHP 520',
                imageUrl: '/images/bakerdan/brazo and cakes/brazo-roll_classic.jpg',
            ),
            $this->product(
                category: 'Brazos and Cakes',
                productName: 'Brazo Slice Box',
                description: 'Brazo de Mercedes slice box, 24-28 pcs.',
                priceLabel: 'PHP 690.00 - 710.00',
                price: 690.00,
                sizesAvailable: 'Box of 24-28 pcs',
                flavorsAvailable: 'Classic - PHP 690 | Ube/Mocha - PHP 700 | Tsokolate - PHP 710',
                imageUrl: '/images/bakerdan/brazo and cakes/brazo_slice.jpg',
            ),
            $this->product(
                category: 'Brazos and Cakes',
                productName: 'Mocha Blueberry Cake (8x12)',
                description: 'Mocha blueberry sliced cake, 8x12 tray.',
                priceLabel: 'PHP 730.00',
                price: 730.00,
                sizesAvailable: '8x12 (24-28 cake slices)',
                flavorsAvailable: 'Mocha Blueberry',
                imageUrl: '/images/bakerdan/brazo and cakes/mocha_blueberry_sliced_cake.jpg',
            ),
            $this->product(
                category: 'Brazos and Cakes',
                productName: 'Mocha Brittle Cake (8x12)',
                description: 'Mocha brittle sliced cake, 8x12 tray.',
                priceLabel: 'PHP 730.00',
                price: 730.00,
                sizesAvailable: '8x12 (24-28 cake slices)',
                flavorsAvailable: 'Mocha Brittle',
                imageUrl: '/images/bakerdan/brazo and cakes/mocha_brittle_sliced_cake.jpg',
            ),
            $this->product(
                category: 'Brazos and Cakes',
                productName: 'Ube Macapuno Cake (8x12)',
                description: 'Ube macapuno or ube custard sliced cake, 8x12 tray.',
                priceLabel: 'PHP 750.00',
                price: 750.00,
                sizesAvailable: '8x12 (24-28 cake slices)',
                flavorsAvailable: 'Ube Macapuno | Ube Custard',
                imageUrl: '/images/bakerdan/brazo and cakes/ube_macapuno_sliced_cake.jpg',
            ),
            $this->product(
                category: 'Brazos and Cakes',
                productName: 'Chocolate Cake (8x12)',
                description: 'Chocolate sliced cake, 8x12 tray.',
                priceLabel: 'PHP 850.00',
                price: 850.00,
                sizesAvailable: '8x12 (24-28 cake slices)',
                flavorsAvailable: 'Chocolate',
                imageUrl: '/images/bakerdan/Cake_Celebration.png',
            ),
            $this->product(
                category: 'Brazos and Cakes',
                productName: 'Red Velvet w/ Creamcheese Frosting (8x12)',
                description: 'Red velvet sliced cake with creamcheese frosting, 8x12 tray.',
                priceLabel: 'PHP 1,200.00',
                price: 1200.00,
                sizesAvailable: '8x12 (24-28 cake slices)',
                flavorsAvailable: 'Red Velvet w/ Creamcheese Frosting',
                imageUrl: '/images/bakerdan/brazo and cakes/red_velvet&strawberry_sliced_cake.jpg',
            ),
        ];
    }

    private function product(
        string $category,
        string $productName,
        string $description,
        string $priceLabel,
        float $price,
        ?string $sizesAvailable,
        ?string $flavorsAvailable,
        string $imageUrl,
    ): array {
        return [
            'category' => $category,
            'product_name' => $productName,
            'description' => $description,
            'price_label' => $priceLabel,
            'price' => $price,
            'sizes_available' => $sizesAvailable,
            'flavors_available' => $flavorsAvailable,
            'image_url' => $imageUrl,
            'image_source' => 'local',
            'is_active' => true,
        ];
    }

    private function productKey(string $category, string $productName): string
    {
        return $category . '|' . $productName;
    }
}
