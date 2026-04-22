<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'price',
        'category',
        'stock',
        'available_sizes',
        'available_flavors',
        'image_url',
        'active',
    ];

    protected $casts = [
        'available_sizes' => 'array',
        'available_flavors' => 'array',
        'active' => 'boolean',
    ];
}
