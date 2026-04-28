<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'inventory_products';

    protected $fillable = [
        'product_id',
        'category',
        'product_name',
        'description',
        'price_label',
        'sizes_available',
        'flavors_available',
        'image_url',
        'image_source',
        'is_active',
        'price',
    ];

    protected $casts = [
        'product_id' => 'integer',
        'is_active' => 'boolean',
        'price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::created(function (self $product): void {
            if ($product->product_id === null) {
                $product->forceFill(['product_id' => $product->getKey()])->saveQuietly();
            }
        });
    }

    public function getNameAttribute(): ?string
    {
        return $this->product_name;
    }

    public function setNameAttribute($value): void
    {
        $this->attributes['product_name'] = $value;
    }

    public function getActiveAttribute(): bool
    {
        return (bool) $this->is_active;
    }

    public function setActiveAttribute($value): void
    {
        $this->attributes['is_active'] = (bool) $value;
    }
}
