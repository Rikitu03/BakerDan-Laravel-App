<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CustomOrderImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class CustomOrderController extends Controller
{
    public function store(Request $request, CustomOrderImageService $customOrderImageService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'size' => 'required|string|in:Small,Medium,Large',
            'quantity' => 'required|integer|min:1|max:99',
            'flavor' => 'required|string|max:100',
            'design_description' => 'nullable|string|max:300',
            'dedication_message' => 'nullable|string|max:300',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'image_url' => 'nullable|string|max:2048',
        ]);

        $validator->after(function ($validator) use ($request): void {
            if (! $this->hasCustomDesignInput($request)) {
                $validator->errors()->add('design_description', 'Please upload a reference image or describe the custom design.');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();
        $cart = Cart::query()->firstOrCreate([
            'user_id' => Auth::id(),
        ]);

        $imagePath = $validated['image_url'] ?? null;

        if ($request->hasFile('image')) {
            $imagePath = $customOrderImageService->storeReferenceImage($request->file('image'));
        }

        $cartItem = $cart->items()->create([
            'product_id' => null,
            'item_type' => 'custom',
            'quantity' => (int) $validated['quantity'],
            'unit_price' => $this->customUnitPrice($validated['size']),
            'product_name' => 'Custom Celebration Order',
            'description' => $this->customDescription($validated['size'], $validated['flavor']),
            'image_url' => $imagePath,
            'design_description' => $validated['design_description'] ?? null,
            'dedication_message' => $validated['dedication_message'] ?? null,
            'size' => $validated['size'],
            'flavor' => $validated['flavor'],
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Custom order draft added to your cart successfully.',
            'data' => [
                'item' => $this->serializeCartItem($cartItem),
                'workflow' => [
                    'estimated_completion' => $this->estimatedCompletionWindow((int) $validated['quantity']),
                    'estimated_price' => round($this->customUnitPrice($validated['size']) * (int) $validated['quantity'], 2),
                    'next_step' => 'Review your custom request in the cart, then continue to checkout and payment.',
                    'requires_review' => true,
                ],
            ],
        ]);
    }

    private function customUnitPrice(string $size): float
    {
        return match ($size) {
            'Small' => 500.00,
            'Medium' => 750.00,
            'Large' => 1000.00,
            default => 500.00,
        };
    }

    private function customDescription(?string $size, ?string $flavor): string
    {
        $normalizedSize = strtolower($size ?: 'medium');
        $normalizedFlavor = strtolower($flavor ?: 'custom');

        return "A {$normalizedFlavor} custom bake with {$normalizedSize} sizing and personalized finishing touches.";
    }

    private function estimatedCompletionWindow(int $quantity): string
    {
        return match (true) {
            $quantity <= 3 => '3-5 business days',
            $quantity <= 8 => '5-7 business days',
            default => '7-10 business days',
        };
    }

    private function hasCustomDesignInput(Request $request): bool
    {
        return $request->hasFile('image')
            || filled($request->input('image_url'))
            || filled($request->input('design_description'));
    }

    private function serializeCartItem(CartItem $item): array
    {
        return [
            'id' => $item->id,
            'source' => 'custom',
            'name' => $item->product_name ?: 'Custom Celebration Order',
            'description' => $item->description,
            'price' => (float) $item->unit_price,
            'quantity' => $item->quantity,
            'size' => $item->size,
            'flavor' => $item->flavor,
            'image' => $this->resolveImageUrl($item->image_url) ?: asset('images/bakerdan/Cake_Celebration.png'),
            'designDescription' => $item->design_description,
            'dedicationMessage' => $item->dedication_message,
        ];
    }

    private function resolveImageUrl(?string $imagePath): ?string
    {
        if (! $imagePath) {
            return null;
        }

        if (str_starts_with($imagePath, 'http://') || str_starts_with($imagePath, 'https://') || str_starts_with($imagePath, '/')) {
            return $imagePath;
        }

        return asset('storage/' . ltrim($imagePath, '/'));
    }
}
