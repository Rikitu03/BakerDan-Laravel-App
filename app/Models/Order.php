<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
        'payment_status',
        'payment_method',
        'payment_provider',
        'payment_reference',
        'payment_session_id',
        'payment_checkout_url',
        'payment_paid_at',
        'payment_expires_at',
        'payment_metadata',
        'shipping_address',
        'promo_id',
        'discount_amount',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'payment_paid_at' => 'datetime',
        'payment_expires_at' => 'datetime',
        'payment_metadata' => 'array',
        'discount_amount' => 'decimal:2',
    ];

    public function isPaymentExpired(): bool
    {
        return $this->payment_expires_at
            && $this->payment_status !== 'paid'
            && now()->greaterThan($this->payment_expires_at);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function promo()
    {
        return $this->belongsTo(Promo::class, 'promo_id');
    }
}
