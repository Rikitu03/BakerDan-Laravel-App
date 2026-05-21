<?php

use Illuminate\Support\Facades\Broadcast;
use App\Models\Conversation;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->user_id === (int) $id;
});

Broadcast::channel('user.{id}', function ($user, $id) {
    return (int) $user->user_id === (int) $id;
});

Broadcast::channel('conversation.{conversationId}', function ($user, $conversationId) {
    if ($user->role === 'admin') {
        return true;
    }

    return Conversation::query()
        ->whereKey($conversationId)
        ->where('customer_id', $user->user_id)
        ->exists();
});

Broadcast::channel('admin.messages', function ($user) {
    return $user->role === 'admin';
});
