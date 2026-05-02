<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $primaryKey = 'user_id';

    protected $fillable = [
        'role',
        'is_active',
    ];

    public function detail()
    {
        return $this->hasOne(UserDetail::class, 'user_id', 'user_id');
    }

    public function customerNotifications()
    {
        return $this->hasMany(CustomerNotification::class, 'user_id', 'user_id');
    }
}
