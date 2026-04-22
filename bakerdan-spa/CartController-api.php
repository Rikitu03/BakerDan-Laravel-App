<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    /**
     * Get cart items for current user
     */
    public function index(): JsonResponse
    {
        $userId = Auth::id();
        
        // TODO: Replace with actual database query
        // Example: $cartItems = CartItem::where('user_id', $userId)
        //     ->with('product')
        //     ->get();

        // Mock data
        $cartItems = [
            [
                'id' => 1,
                'product_id' => 1,
                'name' => 'Korean Garlic Cream Cheese Bun',
                'description' => 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
                'price' => 499.00,
                'quantity' => 2,
                'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
            ],
            [
                'id' => 2,
                'product_id' => 2,
                'name' => 'Korean Garlic Cream Cheese Bun',
                'description' => 'Soft enriched dough with a rich cream cheese filling and glossy golden top.',
                'price' => 499.00,
                'quantity' => 1,
                'image' => 'https://images.unsplash.com/photo-1509440159596-0249088772ff?w=400&h=300&fit=crop',
            ],
        ];

        $subtotal = collect($cartItems)->sum(fn($item) => $item['price'] * $item['quantity']);

        return response()->json([
            'success' => true,
            'data' => [
                'items' => $cartItems,
                'subtotal' => $subtotal,
                'tax' => $subtotal * 0.12, // 12% VAT
                'total' => $subtotal * 1.12,
                'item_count' => count($cartItems),
            ]
        ]);
    }

    /**
     * Add item to cart
     */
    public function add(Request $request, int $productId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:99',
            'size' => 'nullable|string',
            'notes' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = Auth::id();
        
        // TODO: Add to database
        // Example: $cartItem = CartItem::updateOrCreate(
        //     ['user_id' => $userId, 'product_id' => $productId],
        //     ['quantity' => DB::raw('quantity + ' . $request->quantity)]
        // );

        return response()->json([
            'success' => true,
            'message' => 'Item added to cart successfully',
            'data' => [
                'cart_count' => 3, // TODO: Get actual count
            ]
        ]);
    }

    /**
     * Update cart item quantity
     */
    public function update(Request $request, int $itemId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1|max:99',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        // TODO: Update in database
        // Example: $cartItem = CartItem::where('id', $itemId)
        //     ->where('user_id', Auth::id())
        //     ->firstOrFail();
        // $cartItem->update(['quantity' => $request->quantity]);

        return response()->json([
            'success' => true,
            'message' => 'Cart updated successfully'
        ]);
    }

    /**
     * Remove item from cart
     */
    public function remove(int $itemId): JsonResponse
    {
        // TODO: Delete from database
        // Example: CartItem::where('id', $itemId)
        //     ->where('user_id', Auth::id())
        //     ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Item removed from cart'
        ]);
    }

    /**
     * Clear entire cart
     */
    public function clear(): JsonResponse
    {
        $userId = Auth::id();
        
        // TODO: Delete all cart items
        // Example: CartItem::where('user_id', $userId)->delete();

        return response()->json([
            'success' => true,
            'message' => 'Cart cleared successfully'
        ]);
    }
}
