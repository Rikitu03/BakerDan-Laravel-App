<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Promo extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_purchase',
        'max_discount',
        'usage_limit',
        'limit_per_user',
        'usage_count',
        'applicable_products',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'min_purchase' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'usage_limit' => 'integer',
        'limit_per_user' => 'integer',
        'usage_count' => 'integer',
        'applicable_products' => 'array',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Check if the promo is valid for use.
     */
    public function isValid(?float $subtotal = null, ?User $user = null): bool
    {
        return $this->getValidationError($subtotal, $user) === null;
    }

    /**
     * Get error message if promo is invalid.
     */
    public function getValidationError(?float $subtotal = null, ?User $user = null): ?string
    {
        if (!$this->is_active) {
            return "This promo code is inactive.";
        }

        $now = now();
        if ($this->starts_at && $now->lessThan($this->starts_at)) {
            return "This promo code has not started yet.";
        }

        if ($this->expires_at && $now->greaterThan($this->expires_at)) {
            return "This promo code has expired.";
        }

        if ($this->usage_limit !== null && $this->usage_count >= $this->usage_limit) {
            return "This promo code has reached its maximum usage limit.";
        }

        if ($subtotal !== null && $this->min_purchase !== null && $subtotal < (float) $this->min_purchase) {
            return "Minimum purchase of PHP " . number_format($this->min_purchase, 2) . " is required for this promo.";
        }

        if ($user && $this->limit_per_user !== null) {
            $userUsage = Order::query()
                ->where('user_id', $user->user_id)
                ->where('promo_id', $this->id)
                ->whereIn('payment_status', ['paid', 'pending'])
                ->count();
            if ($userUsage >= $this->limit_per_user) {
                return "You have already reached the usage limit for this promo code.";
            }
        }

        return null;
    }

    /**
     * Calculate discount for a given subtotal and items.
     */
    public function calculateDiscount(float $subtotal, array $items = []): float
    {
        $discountableAmount = 0.0;

        // If there are specific applicable products, filter items.
        if (!empty($this->applicable_products) && is_array($this->applicable_products)) {
            $applicableIds = array_map('intval', $this->applicable_products);
            foreach ($items as $item) {
                $productId = isset($item['product_id']) ? (int) $item['product_id'] : null;
                if ($productId && in_array($productId, $applicableIds, true)) {
                    $discountableAmount += (float) ($item['line_total'] ?? (($item['unit_price'] ?? $item['price'] ?? 0) * $item['quantity']) ?? 0);
                }
            }

            // If none of the items are applicable, discount is 0
            if ($discountableAmount <= 0) {
                return 0.0;
            }
        } else {
            // Applies to the whole cart/subtotal
            $discountableAmount = $subtotal;
        }

        // Calculate discount
        $discount = 0.0;
        if ($this->discount_type === 'percentage') {
            $discount = $discountableAmount * ((float) $this->discount_value / 100.0);
            if ($this->max_discount !== null) {
                $discount = min($discount, (float) $this->max_discount);
            }
        } else {
            // Fixed amount discount
            $discount = (float) $this->discount_value;
            // Discount cannot exceed the subtotal/discountable amount
            $discount = min($discount, $discountableAmount);
        }

        return round(max(0.0, $discount), 2);
    }
}
