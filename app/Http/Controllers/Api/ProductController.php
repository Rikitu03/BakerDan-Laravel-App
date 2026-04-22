<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Get all products with optional filtering
     */
    public function index(Request $request): JsonResponse
    {
        // TODO: Replace with actual database query
        // Example: $products = Product::query()
        //     ->when($request->category, fn($q) => $q->where('category', $request->category))
        //     ->when($request->min_price, fn($q) => $q->where('price', '>=', $request->min_price))
        //     ->when($request->max_price, fn($q) => $q->where('price', '<=', $request->max_price))
        //     ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%"))
        //     ->orderBy($request->sort_by ?? 'created_at', $request->sort_order ?? 'desc')
        //     ->paginate($request->per_page ?? 15);

        // Mock data for now
        $products = collect(range(1, 15))->map(function ($id) {
            return [
                'id' => $id,
                'name' => 'Korean Garlic Cream Cheese Bun',
                'description' => 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
                'price' => 499.00,
                'category' => 'Bread',
                'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
                'in_stock' => true,
                'liked' => rand(0, 1) === 1,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $products,
            'pagination' => [
                'total' => 15,
                'per_page' => 15,
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
        // TODO: Replace with actual database query
        // Example: $product = Product::findOrFail($id);

        $product = [
            'id' => $id,
            'name' => 'Korean Garlic Cream Cheese Bun',
            'description' => 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
            'price' => 499.00,
            'category' => 'Bread',
            'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
            'in_stock' => true,
            'ingredients' => ['Flour', 'Cream Cheese', 'Garlic', 'Butter'],
            'allergens' => ['Dairy', 'Gluten'],
            'sizes' => ['Small', 'Medium', 'Large'],
        ];

        return response()->json([
            'success' => true,
            'data' => $product
        ]);
    }

    /**
     * Get all categories
     */
    public function categories(): JsonResponse
    {
        // TODO: Replace with actual database query
        // Example: $categories = Category::all();

        $categories = [
            ['id' => 1, 'name' => 'Bread', 'icon' => '🍞'],
            ['id' => 2, 'name' => 'Patries', 'icon' => '🥐'],
            ['id' => 3, 'name' => 'Cakes', 'icon' => '🎂'],
            ['id' => 4, 'name' => 'Customize', 'icon' => '✨'],
        ];

        return response()->json([
            'success' => true,
            'data' => $categories
        ]);
    }
}
