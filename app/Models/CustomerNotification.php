<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerNotification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'title',
        'message',
        'image_url',
        'payload',
        'is_read',
        'read_at',
    ];

    protected $casts = [
        'payload' => 'array',
        'is_read' => 'boolean',
        'read_at' => 'datetime',
    ];

    /**
     * Send a notification to all admin users.
     */
    public static function notifyAdmins(string $type, string $title, string $message, array $payload = []): void
    {
        $admins = User::where('role', 'admin')->get();
        
        $timestamp = now();
        $rows = $admins->map(function ($admin) use ($type, $title, $message, $payload, $timestamp) {
            return [
                'user_id' => $admin->user_id,
                'type' => $type,
                'title' => $title,
                'message' => $message,
                'payload' => json_encode($payload),
                'is_read' => false,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ];
        })->toArray();

        static::insert($rows);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'user_id');
    }
}
