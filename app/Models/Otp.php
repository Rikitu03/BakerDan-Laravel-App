<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Otp extends Model
{
    use HasFactory;

    protected $primaryKey = 'otp_id';
    public $timestamps = false;

    protected $fillable = [
        'email',
        'otp',
        'created_at',
        'expire_at',
        'purpose',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'expire_at' => 'datetime',
    ];
}
