<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $notifications = $user->customerNotifications()
            ->latest('id')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'notifications' => $notifications->map(function ($notification): array {
                    return [
                        'id' => $notification->id,
                        'type' => $notification->type,
                        'title' => $notification->title,
                        'message' => $notification->message,
                        'image' => $this->resolveImageUrl($notification->image_url),
                        'payload' => $notification->payload ?? [],
                        'unread' => ! $notification->is_read,
                        'created_at' => $notification->created_at?->toIso8601String(),
                        'time' => $notification->created_at?->diffForHumans(),
                    ];
                })->values(),
                'unread_count' => $notifications->where('is_read', false)->count(),
            ],
        ]);
    }

    public function markRead(Request $request, int $notificationId): JsonResponse
    {
        $notification = $request->user()->customerNotifications()
            ->whereKey($notificationId)
            ->firstOrFail();

        if (! $notification->is_read) {
            $notification->forceFill([
                'is_read' => true,
                'read_at' => now(),
            ])->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Notification marked as read.',
        ]);
    }

    public function markAllRead(Request $request): JsonResponse
    {
        $request->user()->customerNotifications()
            ->where('is_read', false)
            ->update([
                'is_read' => true,
                'read_at' => now(),
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Notifications marked as read.',
        ]);
    }

    public function destroy(Request $request, int $notificationId): JsonResponse
    {
        \App\Models\CustomerNotification::query()
            ->where('user_id', $request->user()->user_id)
            ->where('id', $notificationId)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'Notification removed.',
        ]);
    }

    public function destroyAll(Request $request): JsonResponse
    {
        \App\Models\CustomerNotification::query()
            ->where('user_id', $request->user()->user_id)
            ->delete();

        return response()->json([
            'success' => true,
            'message' => 'All notifications removed.',
        ]);
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
