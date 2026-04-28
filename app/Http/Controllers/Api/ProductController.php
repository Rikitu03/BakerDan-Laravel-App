<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all products with optional filtering
     */
    public function index(Request $request): JsonResponse
    {
        $query = Product::query()->where('is_active', true);

        $query->when($request->filled('category'), fn ($builder) => $builder->where('category', $request->string('category')));
        $query->when($request->filled('min_price'), fn ($builder) => $builder->where('price', '>=', (float) $request->input('min_price')));
        $query->when($request->filled('max_price'), fn ($builder) => $builder->where('price', '<=', (float) $request->input('max_price')));
        $query->when($request->filled('search'), function ($builder) use ($request): void {
            $search = $request->string('search');
            $builder->where(function ($nested) use ($search): void {
                $nested->where('product_name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('category', 'like', "%{$search}%");
            });
        });

        $products = $query->orderByDesc('id')->get()->map(function (Product $product): array {
            $imageUrl = $product->image_url ? asset('storage/' . ltrim($product->image_url, '/')) : null;
            return [
                'id' => $product->id,
                'product_id' => $product->product_id ?? $product->id,
                'name' => $product->product_name,
                'product_name' => $product->product_name,
                'description' => $product->description,
                'price' => (float) $product->price,
                'category' => $product->category,
                'image' => $imageUrl,
                'image_url' => $imageUrl,
                'in_stock' => true,
                'is_active' => (bool) $product->is_active,
                'liked' => false,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $products,
            'pagination' => [
                'total' => $products->count(),
                'per_page' => $products->count() ?: 1,
                'current_page' => 1,
                'last_page' => 1,
            ]
        ]);
    }

    /**
     * Get single product details
     */
    public function show(int $id): JsonResponse
    {
        $product = Product::query()->whereKey($id)->where('is_active', true)->firstOrFail();

        $imageUrl = $product->image_url ? asset('storage/' . ltrim($product->image_url, '/')) : null;
        return response()->json([
            'success' => true,
            'data' => [
                'id' => $product->id,
                'product_id' => $product->product_id ?? $product->id,
                'name' => $product->product_name,
                'product_name' => $product->product_name,
                'description' => $product->description,
                'price' => (float) $product->price,
                'category' => $product->category,
                'image' => $imageUrl,
                'image_url' => $imageUrl,
                'in_stock' => true,
                'is_active' => (bool) $product->is_active,
            ]
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
}
