<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ReviewController extends Controller
{
    /**
     * Display a listing of reviews for a product.
     */
    public function index(int $productId): JsonResponse
    {
        $reviews = Review::where('product_id', $productId)
            ->with(['user.detail:user_id,name'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return response()->json($reviews);
    }

    /**
     * Store a newly created review in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'product_id' => 'required|exists:inventory_products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $userId = auth()->id();
        $order = Order::where('id', $validated['order_id'])
            ->where('user_id', $userId)
            ->first();

        if (!$order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if (!in_array($order->status, ['delivered', 'completed'])) {
            return response()->json(['message' => 'You can only review delivered orders.'], 403);
        }

        $hasProduct = $order->items()->where('product_id', $validated['product_id'])->exists();
        if (!$hasProduct) {
            return response()->json(['message' => 'This product is not part of the specified order.'], 403);
        }

        $existingReview = Review::where('user_id', $userId)
            ->where('order_id', $validated['order_id'])
            ->where('product_id', $validated['product_id'])
            ->first();

        if ($existingReview) {
            return response()->json(['message' => 'You have already reviewed this product for this order.'], 422);
        }

        $review = Review::create([
            'user_id' => $userId,
            'order_id' => $validated['order_id'],
            'product_id' => $validated['product_id'],
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return response()->json([
            'message' => 'Review submitted successfully.',
            'review' => $review
        ], 201);
    }

    /**
     * Get reviews by the current authenticated user.
     */
    public function customerReviews(): JsonResponse
    {
        $userId = auth()->id();
        $reviews = Review::where('user_id', $userId)
            ->with(['product:id,product_name,image_url'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'reviews' => $reviews
        ]);
    }
}
