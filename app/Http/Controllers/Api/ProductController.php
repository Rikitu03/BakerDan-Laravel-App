<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Services\ProductOptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private ProductOptionService $productOptions)
    {
    }

    /**
     * Get products with optional filtering and server-side pagination.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min(10, (int) $request->input('per_page', 10)));
        $query = Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->select([
                'id',
                'product_id',
                'product_name',
                'description',
                'price',
                'price_label',
                'category',
                'sizes_available',
                'flavors_available',
                'image_url',
                'image_source',
                'is_active',
                'updated_at',
            ])
            ->where('is_active', true);

        $query->when($request->filled('category'), fn ($builder) => $builder->where('category', (string) $request->string('category')));
        $query->when($request->filled('min_price'), fn ($builder) => $builder->where('price', '>=', (float) $request->input('min_price')));
        $query->when($request->filled('max_price'), fn ($builder) => $builder->where('price', '<=', (float) $request->input('max_price')));
        $query->when($request->filled('search'), function ($builder) use ($request): void {
            $search = (string) $request->string('search');
            $builder->where(function ($nested) use ($search): void {
                $nested->where('product_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%")
                    ->orWhere('sizes_available', 'like', "%{$search}%")
                    ->orWhere('flavors_available', 'like', "%{$search}%");
            });
        });

        match ($request->input('sort')) {
            'price' => $query->orderByDesc('price')->orderBy('product_name'),
            'low-to-high' => $query->orderBy('price')->orderBy('product_name'),
            default => $query->orderBy('category')->orderBy('product_name'),
        };

        $paginator = $query->paginate($perPage);
        $products = $paginator->getCollection()
            ->map(fn (Product $product): array => $this->serializeProduct($product))
            ->values();

        return response()->json([
            'success' => true,
            'data' => $products,
            'pagination' => [
                'total' => $paginator->total(),
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'from' => $paginator->firstItem(),
                'to' => $paginator->lastItem(),
            ]
        ])->header('Cache-Control', 'no-store, max-age=0');
    }

    /**
     * Get single product details
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::query()
            ->withCount('reviews')
            ->withAvg('reviews', 'rating')
            ->whereKey($id)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json([
            'success' => true,
            'data' => $this->serializeProduct($product),
        ]);
    }

    /**
     * Get all categories
     */
    public function categories(): JsonResponse
    {
        $categories = Product::query()
            ->where('is_active', true)
            ->select('category')
            ->distinct()
            ->orderBy('category')
            ->pluck('category')
            ->values()
            ->map(fn (string $category, int $index): array => [
                'id' => $index + 1,
                'name' => $category,
                'icon' => '🍞',
            ]);

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }

    private function resolveImageUrl(?string $imagePath): ?string
    {
        if (!$imagePath) {
            return null;
        }

        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://') || str_starts_with($imagePath, '/')) {
            return $imagePath;
        }

        return asset('storage/' . ltrim($imagePath, '/'));
    }

    private function serializeProduct(Product $product): array
    {
        $imageUrl = $this->resolveImageUrl($product->image_url);

        return [
            'id' => $product->id,
            'product_id' => $product->product_id ?? $product->id,
            'name' => $product->product_name,
            'product_name' => $product->product_name,
            'description' => $product->description,
            'price' => (float) $product->price,
            'price_label' => $product->price_label ?: null,
            'category' => $product->category,
            'sizes_available' => $product->sizes_available,
            'flavors_available' => $product->flavors_available,
            'options' => array_map(function (array $option): array {
                $option['image_url'] = $this->resolveImageUrl($option['image_url'] ?? null);
                return $option;
            }, $this->productOptions->optionsFor($product)),
            'minimum_quantity' => $this->productOptions->minimumQuantityFor($product),
            'category_fallback_image' => $this->categoryFallbackImage($product->category),
            'image_cache_key' => $product->updated_at?->timestamp ?? $product->id,
            'image' => $imageUrl,
            'image_url' => $imageUrl,
            'image_source' => $product->image_source,
            'average_rating' => $product->average_rating,
            'review_count' => $product->review_count,
            'in_stock' => true,
            'is_active' => (bool) $product->is_active,
            'is_custom_only' => false,
            'liked' => false,
            'order_mode' => 'catalog',
            'ordering_guide' => null,
        ];
    }

    private function categoryFallbackImage(?string $category): string
    {
        return '/images/logo/BAKERDAN%20LOGO.jpg';
    }
}
