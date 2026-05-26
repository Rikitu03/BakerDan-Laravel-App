<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Services\CustomOrderImageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class CustomOrderController extends Controller
{
    public function store(Request $request, CustomOrderImageService $customOrderImageService): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'size' => 'required|string|max:120',
            'quantity' => 'required|integer|min:1|max:99',
            'flavor' => 'nullable|string|max:120',
            'design_description' => 'nullable|string|max:600',
            'dedication_message' => 'nullable|string|max:300',
            'image' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
            'image_url' => 'nullable|string|max:2048',
            'guide_section' => 'nullable|string|max:120',
            'product_name' => 'nullable|string|max:255',
            'base_price' => 'nullable|numeric|min:0',
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
            'unit_price' => $this->customUnitPrice($validated['size'], isset($validated['base_price']) ? (float) $validated['base_price'] : null),
            'product_name' => $validated['product_name'] ?? 'Custom Celebration Order',
            'description' => $this->customDescription(
                $validated['size'],
                $validated['flavor'] ?? null,
                $validated['product_name'] ?? null,
                $validated['guide_section'] ?? null,
            ),
            'image_url' => $imagePath,
            'design_description' => $validated['design_description'] ?? null,
            'dedication_message' => $validated['dedication_message'] ?? null,
            'size' => $validated['size'],
            'flavor' => $validated['flavor'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Custom order draft added to your cart successfully.',
            'data' => [
                'item' => $this->serializeCartItem($cartItem),
                'workflow' => [
                    'estimated_completion' => $this->estimatedCompletionWindow((int) $validated['quantity']),
                    'estimated_price' => round($this->customUnitPrice($validated['size'], isset($validated['base_price']) ? (float) $validated['base_price'] : null) * (int) $validated['quantity'], 2),
                    'next_step' => 'Review your custom request in the cart, then continue to checkout and payment.',
                    'requires_review' => true,
                ],
            ],
        ]);
    }

    private function customUnitPrice(string $size, ?float $basePrice = null): float
    {
        if ($basePrice !== null && $basePrice >= 0) {
            return round($basePrice, 2);
        }

        return match ($size) {
            'Small' => 500.00,
            'Medium' => 750.00,
            'Large' => 1000.00,
            default => 500.00,
        };
    }

    private function customDescription(?string $size, ?string $flavor, ?string $productName = null, ?string $guideSection = null): string
    {
        $productLabel = $productName ?: 'Custom Celebration Order';
        $parts = array_filter([
            $guideSection ? 'Guide: ' . $guideSection : null,
            $size ? 'Size: ' . $size : null,
            $flavor ? 'Flavor: ' . $flavor : null,
        ]);

        if ($parts === []) {
            return $productLabel . ' customized to the customer brief.';
        }

        return $productLabel . ' | ' . implode(' | ', $parts);
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
            'basePrice' => (float) $item->unit_price,
            'id' => $item->id,
            'productName' => $item->product_name ?: 'Custom Celebration Order',
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

        if (Storage::disk('public')->exists($imagePath)) {
            return asset('storage/' . ltrim($imagePath, '/'));
        }

        if (Storage::disk('s3')->exists($imagePath)) {
            return Storage::disk('s3')->url($imagePath);
        }

        return asset('storage/' . ltrim($imagePath, '/'));
    }
}
