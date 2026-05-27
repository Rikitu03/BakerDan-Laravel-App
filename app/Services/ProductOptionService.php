<?php

namespace App\Services;

use App\Models\Product;

class ProductOptionService
{
    /**
     * @var array<string, list<array<string, mixed>>>
     */
    private const OPTIONS = [
        'Sugar Cookies|Sugar Cookies' => [
            ['id' => 'fondant-design', 'label' => 'Plain or Marble Fondant Design', 'price' => 65.00, 'size' => '3-3.5 inches', 'flavor' => 'Plain or Marble Fondant Design', 'minimum_quantity' => 25, 'image_url' => '/images/bakerdan/sugar cookies/sugar_cookies.jpg'],
            ['id' => 'customized-character', 'label' => 'Customized Character', 'price' => 70.00, 'size' => '3-3.5 inches', 'flavor' => 'Customized Character', 'minimum_quantity' => 25, 'image_url' => '/images/bakerdan/sugar cookies/Customized_Cookies.png'],
            ['id' => 'edible-print', 'label' => 'Edible Print', 'price' => 85.00, 'size' => '3-3.5 inches', 'flavor' => 'Edible Print', 'minimum_quantity' => 25, 'image_url' => '/images/bakerdan/sugar cookies/sugar_cookies3.jpg'],
        ],
        'Bread|Korean Garlic Creamcheese Bun (KBUN)' => [
            ['id' => 'mini-45g-box-12', 'label' => 'Mini (45g) - Box of 12 pcs', 'price' => 410.00, 'size' => 'Mini (45g) - Box of 12 pcs', 'flavor' => 'Classic', 'image_url' => '/images/bakerdan/bread/Creme_Cheese_Garlic.png'],
            ['id' => 'regular-90g-box-6', 'label' => 'Regular (90g) - Box of 6 pcs', 'price' => 395.00, 'size' => 'Regular (90g) - Box of 6 pcs', 'flavor' => 'Classic', 'image_url' => '/images/bakerdan/bread/Creme_Cheese_Garlic.png'],
            ['id' => 'big-130g-box-4', 'label' => 'Big (130g) - Box of 4 pcs', 'price' => 395.00, 'size' => 'Big (130g) - Box of 4 pcs', 'flavor' => 'Classic', 'image_url' => '/images/bakerdan/bread/Creme_Cheese_Garlic.png'],
        ],
        'Bread|Cinnamon Rolls w/ Creamcheese' => [
            ['id' => 'mini-box-12', 'label' => 'Mini - Box of 12 pcs', 'price' => 405.00, 'size' => 'Mini - Box of 12 pcs', 'image_url' => '/images/bakerdan/pastries/cinnamon_rolls_with_creamcheese.jpg'],
            ['id' => 'big-box-6', 'label' => 'Big - Box of 6 pcs', 'price' => 395.00, 'size' => 'Big - Box of 6 pcs', 'image_url' => '/images/bakerdan/pastries/cinnamon_rolls_with_creamcheese.jpg'],
        ],
        'Bread|Filipino Ensaymada' => [
            ['id' => 'mini-classic', 'label' => 'Mini Classic - Box of 12 pcs', 'price' => 295.00, 'size' => 'Mini (45g) - Box of 12 pcs', 'flavor' => 'Classic', 'image_url' => '/images/bakerdan/bread/filipino_ensaymada.jpg'],
            ['id' => 'mini-bacon', 'label' => 'Mini Bacon - Box of 12 pcs', 'price' => 295.00, 'size' => 'Mini (45g) - Box of 12 pcs', 'flavor' => 'Bacon', 'image_url' => '/images/bakerdan/bread/filipino_ensaymada.jpg'],
            ['id' => 'mini-ube', 'label' => 'Mini Ube - Box of 12 pcs', 'price' => 295.00, 'size' => 'Mini (45g) - Box of 12 pcs', 'flavor' => 'Ube', 'image_url' => '/images/bakerdan/bread/filipino_ensaymada.jpg'],
            ['id' => 'regular-classic', 'label' => 'Regular Classic - Box of 6 pcs', 'price' => 275.00, 'size' => 'Regular (90g) - Box of 6 pcs', 'flavor' => 'Classic', 'image_url' => '/images/bakerdan/bread/filipino_ensaymada.jpg'],
            ['id' => 'regular-bacon', 'label' => 'Regular Bacon - Box of 6 pcs', 'price' => 275.00, 'size' => 'Regular (90g) - Box of 6 pcs', 'flavor' => 'Bacon', 'image_url' => '/images/bakerdan/bread/filipino_ensaymada.jpg'],
            ['id' => 'regular-ube', 'label' => 'Regular Ube - Box of 6 pcs', 'price' => 275.00, 'size' => 'Regular (90g) - Box of 6 pcs', 'flavor' => 'Ube', 'image_url' => '/images/bakerdan/bread/filipino_ensaymada.jpg'],
        ],
        'Bread|Bread Loaves' => [
            ['id' => 'choco-banana-almond', 'label' => 'Choco Banana Almond', 'price' => 220.00, 'size' => 'Loaf', 'flavor' => 'Choco Banana Almond', 'image_url' => '/images/bakerdan/bread/bread_loaves.jpg'],
            ['id' => 'cheesy-garlic', 'label' => 'Cheesy Garlic', 'price' => 200.00, 'size' => 'Loaf', 'flavor' => 'Cheesy Garlic', 'image_url' => '/images/bakerdan/bread/bread_loaves.jpg'],
            ['id' => 'pizza', 'label' => 'Pizza', 'price' => 200.00, 'size' => 'Loaf', 'flavor' => 'Pizza', 'image_url' => '/images/bakerdan/bread/bread_loaves.jpg'],
            ['id' => 'carrot-creamcheese', 'label' => 'Carrot w/ Creamcheese', 'price' => 250.00, 'size' => 'Loaf', 'flavor' => 'Carrot w/ Creamcheese', 'image_url' => '/images/bakerdan/bread/bread_loaves.jpg'],
        ],
        'Pastries|Creampuffs' => [
            ['id' => 'caramel', 'label' => 'Caramel Puff', 'price' => 285.00, 'size' => 'Box of 12 pcs', 'flavor' => 'Caramel', 'image_url' => '/images/bakerdan/pastries/Creme_Puffs.png'],
            ['id' => 'strawberry', 'label' => 'Strawberry Puff', 'price' => 300.00, 'size' => 'Box of 12 pcs', 'flavor' => 'Strawberry', 'image_url' => '/images/bakerdan/pastries/cream_puff.png'],
            ['id' => 'matcha', 'label' => 'Matcha Puff', 'price' => 300.00, 'size' => 'Box of 12 pcs', 'flavor' => 'Matcha', 'image_url' => '/images/bakerdan/pastries/cream_puff.png'],
            ['id' => 'mix', 'label' => 'Mix Puff', 'price' => 310.00, 'size' => 'Box of 12 pcs', 'flavor' => 'Mix', 'image_url' => '/images/bakerdan/pastries/creampuff_mix.jpg'],
        ],
        'Pastries|Eclairs' => [
            ['id' => 'dark-chocolate', 'label' => 'Dark Chocolate', 'price' => 310.00, 'size' => 'Box of 12 pcs', 'flavor' => 'Dark Chocolate', 'image_url' => '/images/bakerdan/pastries/eclair.jpg'],
            ['id' => 'strawberry', 'label' => 'Strawberry', 'price' => 310.00, 'size' => 'Box of 12 pcs', 'flavor' => 'Strawberry', 'image_url' => '/images/bakerdan/pastries/eclair.jpg'],
            ['id' => 'mix', 'label' => 'Mix Eclairs', 'price' => 320.00, 'size' => 'Box of 12 pcs', 'flavor' => 'Mix Eclairs', 'image_url' => '/images/bakerdan/pastries/eclair.jpg'],
        ],
        'Pastries|Muffins' => [
            ['id' => 'chocolate', 'label' => 'Chocolate', 'price' => 350.00, 'size' => 'Box of 6 pcs', 'flavor' => 'Chocolate', 'image_url' => '/images/bakerdan/pastries/muffins.jpg'],
            ['id' => 'blueberry', 'label' => 'Blueberry', 'price' => 340.00, 'size' => 'Box of 6 pcs', 'flavor' => 'Blueberry', 'image_url' => '/images/bakerdan/pastries/muffins.jpg'],
            ['id' => 'corn', 'label' => 'Corn Muffin', 'price' => 340.00, 'size' => 'Box of 6 pcs', 'flavor' => 'Corn Muffin', 'image_url' => '/images/bakerdan/pastries/muffins.jpg'],
            ['id' => 'pumpkin-streusel', 'label' => 'Pumpkin Muffin w/ Streusel', 'price' => 420.00, 'size' => 'Box of 6 pcs', 'flavor' => 'Pumpkin w/ Streusel', 'image_url' => '/images/bakerdan/pastries/muffins.jpg'],
        ],
        'Pastries|Cookie Bites' => [
            ['id' => 'oatmeal-cookies', 'label' => 'Oatmeal Cookies', 'price' => 460.00, 'size' => 'Box of 40 pcs', 'flavor' => 'Oatmeal Cookies', 'image_url' => '/images/bakerdan/pastries/cookie_bites.jpg'],
            ['id' => 'chocolate-chips', 'label' => 'Chocolate Chips', 'price' => 460.00, 'size' => 'Box of 40 pcs', 'flavor' => 'Chocolate Chips', 'image_url' => '/images/bakerdan/pastries/cookie_bites.jpg'],
            ['id' => 'red-velvet-smores', 'label' => 'Red Velvet Smores', 'price' => 460.00, 'size' => 'Box of 40 pcs', 'flavor' => 'Red Velvet Smores', 'image_url' => '/images/bakerdan/pastries/cookie_bites.jpg'],
            ['id' => 'matcha-smores', 'label' => 'Matcha Smores', 'price' => 460.00, 'size' => 'Box of 40 pcs', 'flavor' => 'Matcha Smores', 'image_url' => '/images/bakerdan/pastries/cookie_bites.jpg'],
            ['id' => 'chocolate-smores', 'label' => 'Chocolate Smores', 'price' => 460.00, 'size' => 'Box of 40 pcs', 'flavor' => 'Chocolate Smores', 'image_url' => '/images/bakerdan/pastries/cookie_bites.jpg'],
        ],
        'Pastries|Empanada' => [
            ['id' => 'regular-tuna', 'label' => 'Regular Tuna - Box of 10 pcs', 'price' => 420.00, 'size' => 'Regular - Box of 10 pcs', 'flavor' => 'Tuna', 'image_url' => '/images/bakerdan/pastries/empanada.jpg'],
            ['id' => 'regular-chicken', 'label' => 'Regular Chicken - Box of 10 pcs', 'price' => 420.00, 'size' => 'Regular - Box of 10 pcs', 'flavor' => 'Chicken', 'image_url' => '/images/bakerdan/pastries/empanada.jpg'],
            ['id' => 'regular-pork', 'label' => 'Regular Pork - Box of 10 pcs', 'price' => 460.00, 'size' => 'Regular - Box of 10 pcs', 'flavor' => 'Pork', 'image_url' => '/images/bakerdan/pastries/empanada.jpg'],
            ['id' => 'mini-tuna', 'label' => 'Mini Tuna - Box of 18 pcs', 'price' => 450.00, 'size' => 'Mini - Box of 18 pcs', 'flavor' => 'Tuna', 'image_url' => '/images/bakerdan/pastries/empanada.jpg'],
            ['id' => 'mini-chicken', 'label' => 'Mini Chicken - Box of 18 pcs', 'price' => 450.00, 'size' => 'Mini - Box of 18 pcs', 'flavor' => 'Chicken', 'image_url' => '/images/bakerdan/pastries/empanada.jpg'],
        ],
        'Pastries|Mini Donuts' => [
            ['id' => 'creme-brulee', 'label' => 'Creme Brulee', 'price' => 420.00, 'size' => 'Box of 12 pcs', 'flavor' => 'Creme Brulee', 'image_url' => '/images/bakerdan/pastries/mini_donuts.jpg'],
            ['id' => 'fruity-custard', 'label' => 'Fruity w/ Custard', 'price' => 450.00, 'size' => 'Box of 12 pcs', 'flavor' => 'Fruity w/ Custard', 'image_url' => '/images/bakerdan/pastries/mini_donut_fruitycustard.jpg'],
        ],
        'Pastries|Glass Dessert Cups' => [
            ['id' => 'mousse-mango', 'label' => 'Mousse - Mango', 'price' => 1375.00, 'size' => '24 pcs glass desserts | 60ml', 'flavor' => 'Mango Mousse', 'image_url' => '/images/bakerdan/pastries/glass_dessert_cups_mousse_mango.jpg'],
            ['id' => 'mousse-chocolate', 'label' => 'Mousse - Chocolate', 'price' => 1375.00, 'size' => '24 pcs glass desserts | 60ml', 'flavor' => 'Chocolate Mousse'],
            ['id' => 'mousse-cookies-cream', 'label' => 'Mousse - Cookies & Cream', 'price' => 1375.00, 'size' => '24 pcs glass desserts | 60ml', 'flavor' => 'Cookies & Cream Mousse', 'image_url' => '/images/bakerdan/pastries/glass_dessert_cups_mousse_cookies&cream.jpg'],
            ['id' => 'mousse-strawberry', 'label' => 'Mousse - Strawberry', 'price' => 1375.00, 'size' => '24 pcs glass desserts | 60ml', 'flavor' => 'Strawberry Mousse', 'image_url' => '/images/bakerdan/pastries/glass_dessert_cups_mousse_strawberry.jpg'],
            ['id' => 'mousse-biscoff', 'label' => 'Mousse - Biscoff', 'price' => 1475.00, 'size' => '24 pcs glass desserts | 60ml', 'flavor' => 'Biscoff Mousse', 'image_url' => '/images/bakerdan/pastries/glass_dessert_cups_mousse_biscoff.jpg'],
            ['id' => 'cheesecake-mango', 'label' => 'Cheesecake - Mango', 'price' => 1475.00, 'size' => '24 pcs glass desserts | 60ml', 'flavor' => 'Mango Cheesecake', 'image_url' => '/images/bakerdan/pastries/glass_dessert_cups_cheescake_mango.jpg'],
            ['id' => 'cheesecake-biscoff', 'label' => 'Cheesecake - Biscoff', 'price' => 1600.00, 'size' => '24 pcs glass desserts | 60ml', 'flavor' => 'Biscoff Cheesecake', 'image_url' => '/images/bakerdan/pastries/glass_dessert_cups_cheescake_biscoff.jpg'],
            ['id' => 'cheesecake-strawberry', 'label' => 'Cheesecake - Strawberry', 'price' => 1475.00, 'size' => '24 pcs glass desserts | 60ml', 'flavor' => 'Strawberry Cheesecake', 'image_url' => '/images/bakerdan/pastries/glass_dessert_cups_cheescake_strawberry.jpg'],
            ['id' => 'cheesecake-blueberry', 'label' => 'Cheesecake - Blueberry', 'price' => 1475.00, 'size' => '24 pcs glass desserts | 60ml', 'flavor' => 'Blueberry Cheesecake'],
            ['id' => 'cheesecake-oreo', 'label' => 'Cheesecake - Oreo', 'price' => 1475.00, 'size' => '24 pcs glass desserts | 60ml', 'flavor' => 'Oreo Cheesecake', 'image_url' => '/images/bakerdan/pastries/glass_dessert_cups_cheesecake_oreo.jpg'],
        ],
        'Brazos and Cakes|Brazo Cups' => [
            ['id' => 'classic', 'label' => 'Classic', 'price' => 590.00, 'size' => 'Box of 24 pcs', 'flavor' => 'Classic', 'image_url' => '/images/bakerdan/brazo and cakes/brazo_cups.jpg'],
            ['id' => 'mix', 'label' => 'Mix', 'price' => 620.00, 'size' => 'Box of 24 pcs', 'flavor' => 'Mix', 'image_url' => '/images/bakerdan/brazo and cakes/brazo_cups.jpg'],
        ],
        'Brazos and Cakes|Brazo Roll' => [
            ['id' => 'classic', 'label' => 'Classic', 'price' => 500.00, 'size' => 'Roll', 'flavor' => 'Classic', 'image_url' => '/images/bakerdan/brazo and cakes/brazo-roll_classic.jpg'],
            ['id' => 'ube', 'label' => 'Ube', 'price' => 520.00, 'size' => 'Roll', 'flavor' => 'Ube', 'image_url' => '/images/bakerdan/brazo and cakes/brazo-roll_classic.jpg'],
            ['id' => 'tsokolate', 'label' => 'Tsokolate', 'price' => 530.00, 'size' => 'Roll', 'flavor' => 'Tsokolate', 'image_url' => '/images/bakerdan/brazo and cakes/brazo-roll_chocolate.jpg'],
            ['id' => 'mocha', 'label' => 'Mocha', 'price' => 520.00, 'size' => 'Roll', 'flavor' => 'Mocha', 'image_url' => '/images/bakerdan/brazo and cakes/brazo-roll_classic.jpg'],
        ],
        'Brazos and Cakes|Brazo Slice Box' => [
            ['id' => 'classic', 'label' => 'Classic', 'price' => 690.00, 'size' => 'Box of 24-28 pcs', 'flavor' => 'Classic', 'image_url' => '/images/bakerdan/brazo and cakes/brazo_slice.jpg'],
            ['id' => 'ube', 'label' => 'Ube', 'price' => 700.00, 'size' => 'Box of 24-28 pcs', 'flavor' => 'Ube', 'image_url' => '/images/bakerdan/brazo and cakes/brazo_slice.jpg'],
            ['id' => 'mocha', 'label' => 'Mocha', 'price' => 700.00, 'size' => 'Box of 24-28 pcs', 'flavor' => 'Mocha', 'image_url' => '/images/bakerdan/brazo and cakes/brazo_slice.jpg'],
            ['id' => 'tsokolate', 'label' => 'Tsokolate', 'price' => 710.00, 'size' => 'Box of 24-28 pcs', 'flavor' => 'Tsokolate', 'image_url' => '/images/bakerdan/brazo and cakes/brazo_slice.jpg'],
        ],
        'Brazos and Cakes|Ube Macapuno Cake (8x12)' => [
            ['id' => 'ube-macapuno', 'label' => 'Ube Macapuno', 'price' => 750.00, 'size' => '8x12 (24-28 cake slices)', 'flavor' => 'Ube Macapuno', 'image_url' => '/images/bakerdan/brazo and cakes/ube_macapuno_sliced_cake.jpg'],
            ['id' => 'ube-custard', 'label' => 'Ube Custard', 'price' => 750.00, 'size' => '8x12 (24-28 cake slices)', 'flavor' => 'Ube Custard', 'image_url' => '/images/bakerdan/brazo and cakes/ube_macapuno_sliced_cake.jpg'],
        ],
    ];

    /**
     * @return list<array<string, mixed>>
     */
    public function optionsFor(Product $product): array
    {
        return array_map(
            fn (array $option): array => $this->normalizeOption($product, $option),
            self::OPTIONS[$this->productKey($product)] ?? [],
        );
    }

    public function optionFor(Product $product, ?string $optionId): ?array
    {
        $options = $this->optionsFor($product);

        if ($options === []) {
            return null;
        }

        if (filled($optionId)) {
            foreach ($options as $option) {
                if ($option['id'] === $optionId) {
                    return $option;
                }
            }

            return null;
        }

        return $options[0];
    }

    public function minimumQuantityFor(Product $product): int
    {
        $options = $this->optionsFor($product);

        if ($options === []) {
            return 1;
        }

        return min(array_map(fn (array $option): int => (int) $option['minimum_quantity'], $options));
    }

    /**
     * @param array<string, mixed> $option
     * @return array<string, mixed>
     */
    private function normalizeOption(Product $product, array $option): array
    {
        $price = round((float) $option['price'], 2);

        // Determine image url source (option value or product)
        $imageUrl = $option['image_url'] ?? $product->image_url ?? null;

        // If not null and not already a full URL, prefix with AWS_URL
        if ($imageUrl && ! preg_match('#^https?://#', $imageUrl)) {
            $path = ltrim($imageUrl, '/'); // ensure no leading slash
            $awsRoot = rtrim(config('app.aws_url', ''), '/');

            if ($awsRoot !== '') {
                $imageUrl = $awsRoot . '/' . $path;
            } else {
                // fallback to local path with leading slash
                $imageUrl = '/' . $path;
            }
        }

        return [
            'id' => (string) $option['id'],
            'label' => (string) $option['label'],
            'price' => $price,
            'price_label' => 'PHP ' . number_format($price, 2),
            'size' => $option['size'] ?? null,
            'flavor' => $option['flavor'] ?? null,
            'minimum_quantity' => (int) ($option['minimum_quantity'] ?? 1),
            'image_url' => $imageUrl,
        ];
    }

    private function productKey(Product $product): string
    {
        return $product->category . '|' . $product->product_name;
    }
}
