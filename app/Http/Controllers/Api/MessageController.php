<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MessageController extends Controller
{
    /**
     * Get all conversations (Admin) or the active one (Customer)
     */
    public function getConversations(): JsonResponse
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            $conversations = Conversation::with(['customer.detail', 'lastMessage'])
                ->orderBy('last_message_at', 'desc')
                ->get();
        } else {
            $conversations = Conversation::with(['lastMessage'])
                ->where('customer_id', $user->user_id)
                ->orderBy('last_message_at', 'desc')
                ->get();
        }

        return response()->json($conversations);
    }

    /**
     * Get messages for a specific conversation
     */
    public function getMessages(int $id): JsonResponse
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        // Security: Ensure customers can only see their own conversations
        if ($user->role !== 'admin' && $conversation->customer_id !== $user->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $messages = Message::where('conversation_id', $id)
            ->with('sender.detail')
            ->orderBy('created_at', 'asc')
            ->get();

        // Mark messages as read
        Message::where('conversation_id', $id)
            ->where('sender_id', '!=', $user->user_id)
            ->update(['is_read' => true]);

        return response()->json($messages);
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'content' => 'required|string',
            'conversation_id' => 'nullable|integer',
            'customer_id' => 'nullable|integer', // Admin can specify which customer to message
        ]);

        $user = Auth::user();
        $conversationId = $request->conversation_id;

        DB::beginTransaction();
        try {
            // If no conversation_id, find or create one for the customer
            if (!$conversationId) {
                $customerId = ($user->role === 'admin') ? $request->customer_id : $user->user_id;
                
                if (!$customerId) {
                    return response()->json(['error' => 'Customer ID required'], 422);
                }

                $conversation = Conversation::firstOrCreate([
                    'customer_id' => $customerId,
                    'status' => 'active'
                ]);
                $conversationId = $conversation->id;
            } else {
                $conversation = Conversation::findOrFail($conversationId);
            }

            $message = Message::create([
                'conversation_id' => $conversationId,
                'sender_id' => $user->user_id,
                'content' => $request->content,
            ]);

            $conversation->update(['last_message_at' => now()]);

            // Broadcast real-time message
            event(new \App\Events\MessageSent($message));

            DB::commit();

            return response()->json($message->load('sender.detail'));
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Mark all messages in a conversation as read
     */
    public function markAsRead(int $id): JsonResponse
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        if ($user->role !== 'admin' && $conversation->customer_id !== $user->user_id) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        Message::where('conversation_id', $id)
            ->where('sender_id', '!=', $user->user_id)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }
}
