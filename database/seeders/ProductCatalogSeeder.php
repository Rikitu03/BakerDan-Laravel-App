<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;

class ProductCatalogSeeder extends Seeder
{
    private const PRODUCTS = [
        ['product_name' => 'Creme Cheese Garlic (Box of 6 pcs)', 'category' => 'Breads', 'price_label' => 'PHP 280.00', 'sizes_available' => 'N/A', 'flavors_available' => 'N/A', 'image_source' => 'received_1732155128165064.webp'],
        ['product_name' => 'Ube / Classic Potato Ensaymada', 'category' => 'Breads', 'price_label' => 'PHP 160.00', 'sizes_available' => 'N/A', 'flavors_available' => 'Ube, Classic Potato', 'image_source' => 'received_1732155128165064.webp'],
        ['product_name' => 'Cinnamon Rolls (Box of 12 pcs)', 'category' => 'Pastries', 'price_label' => 'PHP 360.00', 'sizes_available' => 'N/A', 'flavors_available' => 'N/A', 'image_source' => 'received_1732155128165064.webp'],
        ['product_name' => 'Golden Egg Pie', 'category' => 'Pastries', 'price_label' => 'PHP 260.00', 'sizes_available' => '8" round', 'flavors_available' => 'N/A', 'image_source' => 'received_1732155128165064.webp'],
        ['product_name' => 'Eclair (Box of 12 pcs)', 'category' => 'Pastries', 'price_label' => 'PHP 180.00', 'sizes_available' => 'N/A', 'flavors_available' => 'N/A', 'image_source' => 'received_1732155128165064.webp'],
        ['product_name' => 'Mix Tarts (Box of 11 pcs)', 'category' => 'Pastries', 'price_label' => 'PHP 200.00', 'sizes_available' => 'N/A', 'flavors_available' => 'N/A', 'image_source' => 'received_1732155128165064.webp'],
        ['product_name' => 'Barkada Macaroons (Box of 35 pcs)', 'category' => 'Pastries', 'price_label' => 'PHP 180.00', 'sizes_available' => 'N/A', 'flavors_available' => 'N/A', 'image_source' => 'received_1732155128165064.webp'],
        ['product_name' => 'Creme Puffs (Box of 12 pcs)', 'category' => 'Pastries', 'price_label' => 'PHP 160.00', 'sizes_available' => 'N/A', 'flavors_available' => 'N/A', 'image_source' => 'received_1732155128165064.webp'],
        ['product_name' => 'Wallnut Brownies (Box of 15 pcs)', 'category' => 'Pastries', 'price_label' => 'PHP 230.00', 'sizes_available' => 'N/A', 'flavors_available' => 'N/A', 'image_source' => 'received_1732155128165064.webp'],
        ['product_name' => 'Piped Cupcakes (12 pcs)', 'category' => 'Pastries', 'price_label' => 'PHP 750.00', 'sizes_available' => 'N/A', 'flavors_available' => 'Up to 3 colors', 'image_source' => 'received_1303018598395719.jpeg'],
        ['product_name' => 'Fondant Cupcakes (12 pcs)', 'category' => 'Pastries', 'price_label' => 'PHP 950.00 - 1,000.00', 'sizes_available' => 'N/A', 'flavors_available' => 'Up to 3 designs', 'image_source' => 'received_1303018598395719.jpeg'],
        ['product_name' => 'Sugar Cookies (25 pcs)', 'category' => 'Pastries', 'price_label' => 'PHP 1,500.00', 'sizes_available' => '3" inch', 'flavors_available' => 'Up to 5 designs', 'image_source' => 'received_1303018598395719.jpeg'],
        ['product_name' => 'Cupcake Tower (11 pcs)', 'category' => 'Pastries', 'price_label' => 'PHP 1,300.00', 'sizes_available' => '3 oz cupcakes', 'flavors_available' => 'N/A', 'image_source' => 'received_1303018598395719.jpeg'],
        ['product_name' => 'Creampuff Tower (36 to 40 pcs)', 'category' => 'Pastries', 'price_label' => 'PHP 2,500.00', 'sizes_available' => 'N/A', 'flavors_available' => 'N/A', 'image_source' => 'received_1303018598395719.jpeg'],
        ['product_name' => 'Custard Cake', 'category' => 'Cakes', 'price_label' => 'PHP 200.00', 'sizes_available' => '8" round', 'flavors_available' => 'N/A', 'image_source' => 'received_1732155128165064.webp'],
        ['product_name' => 'Brazo de Ube / Mercedes', 'category' => 'Cakes', 'price_label' => 'PHP 300.00', 'sizes_available' => 'N/A', 'flavors_available' => 'Ube, Mercedes', 'image_source' => 'received_1732155128165064.webp'],
        ['product_name' => 'Cake Roll', 'category' => 'Cakes', 'price_label' => 'PHP 300.00', 'sizes_available' => 'N/A', 'flavors_available' => 'Ube, Mocha, Choco', 'image_source' => 'received_1732155128165064.webp'],
        ['product_name' => 'Soft Icing Cakes (Round Cakes Promo)', 'category' => 'Cakes', 'price_label' => 'PHP 2,300.00 - 3,100.00', 'sizes_available' => '6"x6", 8"x4", 8"x6", 10"x4"', 'flavors_available' => 'Mocha Buttercake, Ube Buttercake, Buttercake, Pandan, Dark Chocolate Moist (+PHP 100)', 'image_source' => 'received_1378296820985078.jpeg'],
        ['product_name' => 'Sheet Cakes & Number Cakes', 'category' => 'Cakes', 'price_label' => 'PHP 1,850.00 - 2,300.00', 'sizes_available' => '8"x12", 12"x12"', 'flavors_available' => 'Chocolate Chiffon, Mocha Chiffon, Ube Chiffon, Plain Vanilla Chiffon, Funfetti, Pandan Chiffon', 'image_source' => 'received_26342431045415063.jpeg'],
        ['product_name' => 'Fondant Cakes', 'category' => 'Cakes', 'price_label' => 'PHP 2,500.00 - 3,500.00', 'sizes_available' => '6"x6", 8"x4", 8"x6", 10"x5"', 'flavors_available' => 'Moist Chocolate, Mocha Buttercake, Ube Buttercake, Plain Buttercake, Funfetti (+PHP 50), Redvelvet (+PHP 100), Carrot (+PHP 100)', 'image_source' => 'received_975113635046519.jpeg'],
        ['product_name' => '2-Tier Floral Naked Cake', 'category' => 'Cakes', 'price_label' => 'PHP 3,950.00', 'sizes_available' => 'Bottom: 8"x5", Top: 6"x5"', 'flavors_available' => 'N/A', 'image_source' => 'received_1971047923537159.jpeg'],
        ['product_name' => '2-Tier Fondant Raffles', 'category' => 'Cakes', 'price_label' => 'PHP 4,300.00', 'sizes_available' => 'Bottom: 8"x5", Top: 6"x5"', 'flavors_available' => 'N/A', 'image_source' => 'received_1971047923537159.jpeg'],
        ['product_name' => '2-Tier Full Fondant with 3D Characters', 'category' => 'Cakes', 'price_label' => 'PHP 4,600.00', 'sizes_available' => 'Bottom: 8"x5", Top: 6"x5"', 'flavors_available' => 'N/A', 'image_source' => 'received_1971047923537159.jpeg'],
        ['product_name' => '3-Tier Vintage with Few Fondant', 'category' => 'Cakes', 'price_label' => 'PHP 5,600.00', 'sizes_available' => 'Bottom: 10"x5", Middle: 8"x6", Top: 6"x6"', 'flavors_available' => 'N/A', 'image_source' => 'received_1971047923537159.jpeg'],
        ['product_name' => '3-Tier Full Fondant with Floral', 'category' => 'Cakes', 'price_label' => 'PHP 5,800.00', 'sizes_available' => 'Bottom: 10"x5", Middle: 8"x6", Top: 6"x6"', 'flavors_available' => 'N/A', 'image_source' => 'received_1971047923537159.jpeg'],
        ['product_name' => '3-Tier Full Fondant Wedding Cake', 'category' => 'Cakes', 'price_label' => 'PHP 6,500.00+', 'sizes_available' => 'Bottom: 10"x5", Middle: 8"x6", Top: 6"x6"', 'flavors_available' => 'N/A', 'image_source' => 'received_1971047923537159.jpeg'],
        ['product_name' => 'Money Cake Add-on', 'category' => 'Customization', 'price_label' => '+ PHP 250.00', 'sizes_available' => 'N/A', 'flavors_available' => 'N/A', 'image_source' => 'received_1378296820985078.jpeg, received_975113635046519.jpeg'],
        ['product_name' => 'Soft Icing with Fondant Details', 'category' => 'Customization', 'price_label' => 'Rates to be discussed', 'sizes_available' => 'N/A', 'flavors_available' => 'N/A', 'image_source' => 'received_1378296820985078.jpeg'],
        ['product_name' => 'Additional Fondant Details & Edible Print', 'category' => 'Customization', 'price_label' => 'Additional fee applies', 'sizes_available' => 'N/A', 'flavors_available' => 'N/A', 'image_source' => 'received_26342431045415063.jpeg'],
    ];

    private ?array $catalogImageCandidates = null;

    public function run(): void
    {
        $activeSignatures = [];

        foreach (self::PRODUCTS as $row) {
            $category = $this->normalizeCategory($row['category']);
            $signature = $this->signature($category, $row['product_name']);
            $activeSignatures[$signature] = true;

            Product::query()->updateOrCreate(
                [
                    'category' => $category,
                    'product_name' => $row['product_name'],
                ],
                [
                    'description' => $this->buildDescription($category, $row['sizes_available'], $row['flavors_available']),
                    'price' => $this->extractBasePrice($row['price_label']),
                    'price_label' => $row['price_label'],
                    'sizes_available' => $this->nullableValue($row['sizes_available']),
                    'flavors_available' => $this->nullableValue($row['flavors_available']),
                    'image_url' => $this->resolveImageUrl($category, $row['product_name']),
                    'image_source' => $row['image_source'],
                    'is_active' => true,
                ],
            );
        }

        Product::query()->get()->each(function (Product $product) use ($activeSignatures): void {
            $signature = $this->signature($product->category, $product->product_name);

            if (!isset($activeSignatures[$signature])) {
                $product->delete();
            }
        });
    }

    private function normalizeCategory(string $category): string
    {
        return match ($category) {
            'Breads' => 'Bread',
            'Customization' => 'Customize',
            default => $category,
        };
    }

    private function nullableValue(?string $value): ?string
    {
        $normalized = trim((string) $value);

        return $normalized === '' || strtoupper($normalized) === 'N/A'
            ? null
            : $normalized;
    }

    private function extractBasePrice(string $priceLabel): float
    {
        preg_match('/\d[\d,]*\.?\d*/', $priceLabel, $matches);

        if (!isset($matches[0])) {
            return 0.0;
        }

        return (float) str_replace(',', '', $matches[0]);
    }

    private function buildDescription(string $category, ?string $sizes, ?string $flavors): string
    {
        $parts = [];

        if ($this->nullableValue($sizes)) {
            $parts[] = 'Sizes: ' . $this->nullableValue($sizes);
        }

        if ($this->nullableValue($flavors)) {
            $parts[] = 'Flavors/Notes: ' . $this->nullableValue($flavors);
        }

        if ($parts === []) {
            return match ($category) {
                'Bread' => 'Fresh bakery bread available from the catalog database.',
                'Pastries' => 'Bakerdan pastry item available from the catalog database.',
                'Cakes' => 'Bakerdan cake item available from the catalog database.',
                'Customize' => 'Customization add-on with pricing handled according to the catalog details.',
                default => 'Bakerdan catalog item available from the database.',
            };
        }

        return implode(' ', $parts);
    }

    private function resolveImageUrl(string $category, string $productName): string
    {
        $normalizedProductName = $this->normalizeProductName($productName);

        foreach ($this->catalogImageCandidates() as $candidate) {
            if ($candidate['normalized'] === $normalizedProductName) {
                return $candidate['path'];
            }
        }

        foreach ($this->catalogImageCandidates() as $candidate) {
            if (strlen($candidate['normalized']) < 8) {
                continue;
            }

            if (str_contains($normalizedProductName, $candidate['normalized'])) {
                return $candidate['path'];
            }
        }

        return match ($category) {
            'Bread' => '/images/bakerdan/Bread.png',
            'Pastries' => '/images/bakerdan/Customized_Cookies.png',
            'Cakes', 'Customize' => '/images/bakerdan/Cake_Celebration.png',
            default => '/images/bakerdan/Bread.png',
        };
    }

    private function normalizeProductName(string $productName): string
    {
        $normalized = strtolower($productName);
        $normalized = preg_replace('/[^a-z0-9]+/', ' ', $normalized) ?? $normalized;

        return trim($normalized);
    }

    private function catalogImageCandidates(): array
    {
        if ($this->catalogImageCandidates !== null) {
            return $this->catalogImageCandidates;
        }

        $directory = public_path('images/bakerdan');

        if (!is_dir($directory)) {
            return $this->catalogImageCandidates = [];
        }

        return $this->catalogImageCandidates = collect(File::files($directory))
            ->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['png', 'jpg', 'jpeg', 'webp'], true))
            ->map(function ($file): array {
                $filename = pathinfo($file->getFilename(), PATHINFO_FILENAME);

                return [
                    'normalized' => $this->normalizeProductName($filename),
                    'path' => '/images/bakerdan/' . $file->getFilename(),
                ];
            })
            ->filter(fn (array $candidate): bool => $candidate['normalized'] !== '')
            ->sortByDesc(fn (array $candidate): int => strlen($candidate['normalized']))
            ->values()
            ->all();
    }

    private function signature(string $category, string $productName): string
    {
        return $category . '|' . $productName;
    }
}
