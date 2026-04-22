<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class CustomOrderController extends Controller
{
    /**
     * Create a new custom order
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'size' => 'required|string|in:Small,Medium,Large',
            'quantity' => 'required|integer|min:1|max:99',
            'flavor' => 'required|string',
            'design_description' => 'nullable|string|max:300',
            'dedication_message' => 'nullable|string|max:300',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $userId = Auth::id();
        
        // Handle image upload if present
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('custom-orders', 'public');
        }

        // TODO: Save to database
        // Example: $customOrder = CustomOrder::create([
        //     'user_id' => $userId,
        //     'size' => $request->size,
        //     'quantity' => $request->quantity,
        //     'flavor' => $request->flavor,
        //     'design_description' => $request->design_description,
        //     'dedication_message' => $request->dedication_message,
        //     'image_path' => $imagePath,
        //     'status' => 'pending',
        //     'estimated_price' => $this->calculateEstimatedPrice($request),
        // ]);

        return response()->json([
            'success' => true,
            'message' => 'Custom order created successfully',
            'data' => [
                'order_id' => 123, // TODO: Return actual order ID
                'estimated_price' => 1500.00, // TODO: Calculate based on size/quantity
                'estimated_completion' => '3-5 business days',
            ]
        ]);
    }

    /**
     * Calculate estimated price for custom order
     */
    private function calculateEstimatedPrice(Request $request): float
    {
        $basePrice = 500;
        
        // Size multiplier
        $sizeMultiplier = match($request->size) {
            'Small' => 1.0,
            'Medium' => 1.5,
            'Large' => 2.0,
            default => 1.0,
        };

        return $basePrice * $sizeMultiplier * $request->quantity;
    }
}
