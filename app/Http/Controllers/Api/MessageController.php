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
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

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
                ->withCount([
                    'messages as unread_count' => fn ($query) => $query
                        ->where('sender_id', '!=', $user->user_id)
                        ->where('is_read', false),
                ])
                ->orderBy('last_message_at', 'desc')
                ->limit(80)
                ->get();
        } else {
            $conversations = Conversation::with(['customer.detail', 'lastMessage'])
                ->withCount([
                    'messages as unread_count' => fn ($query) => $query
                        ->where('sender_id', '!=', $user->user_id)
                        ->where('is_read', false),
                ])
                ->where('customer_id', $user->user_id)
                ->orderBy('last_message_at', 'desc')
                ->get();
        }

        return response()->json(
            $conversations->map(fn (Conversation $conversation): array => $this->serializeConversation($conversation))->values()
        );
    }

    /**
     * Get messages for a specific conversation
     */
    public function getMessages(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        if (! $this->canAccessConversation($user, $conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        $afterId = max(0, (int) $request->query('after_id', 0));
        $limit = min(max((int) $request->query('limit', 80), 1), 120);

        $query = Message::query()
            ->where('conversation_id', $id)
            ->with('sender.detail');

        if ($afterId > 0) {
            $query->where('id', '>', $afterId)
                ->orderBy('id');
        } else {
            $query->latest('id')
                ->limit($limit);
        }

        $messages = $query->get()
            ->sortBy('id')
            ->values();

        Message::query()
            ->where('conversation_id', $id)
            ->where('sender_id', '!=', $user->user_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(
            $messages->map(fn (Message $message): array => $this->serializeMessage($message))->values()
        );
    }

    /**
     * Send a message
     */
    public function sendMessage(Request $request): JsonResponse
    {
        $request->validate([
            'content' => ['required', 'string', 'max:2000'],
            'conversation_id' => 'nullable|integer',
            'customer_id' => 'nullable|integer', // Admin can specify which customer to message
        ]);

        $user = Auth::user();
        $content = trim((string) $request->input('content'));
        if ($content === '') {
            return response()->json(['error' => 'Message content is required.'], 422);
        }

        try {
            $message = DB::transaction(function () use ($request, $user, $content): Message {
                $conversationId = $request->integer('conversation_id');

                if (! $conversationId) {
                    $customerId = $user->role === 'admin'
                        ? $request->integer('customer_id')
                        : $user->user_id;

                    if (! $customerId) {
                        abort(422, 'Customer ID required.');
                    }

                    if ($user->role === 'admin') {
                        $customer = User::query()
                            ->whereKey($customerId)
                            ->where('role', 'customer')
                            ->firstOrFail();
                        $customerId = $customer->user_id;
                    }

                    $conversation = Conversation::firstOrCreate(
                        ['customer_id' => $customerId, 'status' => 'active'],
                        ['last_message_at' => now()]
                    );
                } else {
                    $conversation = Conversation::findOrFail($conversationId);
                }

                if (! $this->canAccessConversation($user, $conversation)) {
                    abort(403, 'Unauthorized.');
                }

                $message = Message::query()->create([
                    'conversation_id' => $conversation->id,
                    'sender_id' => $user->user_id,
                    'content' => Str::limit($content, 2000, ''),
                ]);

                $conversation->update(['last_message_at' => $message->created_at]);

                return $message->load('sender.detail', 'conversation.customer.detail');
            });

            if ($this->shouldBroadcastMessages()) {
                event(new \App\Events\MessageSent($message));
            }

            return response()->json($this->serializeMessage($message));
        } catch (HttpExceptionInterface $e) {
            return response()->json(['error' => $e->getMessage()], $e->getStatusCode());
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unable to send message right now.'], 500);
        }
    }

    /**
     * Mark all messages in a conversation as read
     */
    public function markAsRead(int $id): JsonResponse
    {
        $user = Auth::user();
        $conversation = Conversation::findOrFail($id);

        if (! $this->canAccessConversation($user, $conversation)) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        Message::query()
            ->where('conversation_id', $id)
            ->where('sender_id', '!=', $user->user_id)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        return response()->json(['success' => true]);
    }

    private function canAccessConversation(User $user, Conversation $conversation): bool
    {
        return $user->role === 'admin' || (int) $conversation->customer_id === (int) $user->user_id;
    }

    private function shouldBroadcastMessages(): bool
    {
        return ! in_array((string) config('broadcasting.default'), ['log', 'null', ''], true);
    }

    private function serializeConversation(Conversation $conversation): array
    {
        $customerName = $conversation->customer?->detail?->name ?? 'Customer';
        $lastMessage = $conversation->lastMessage;

        return [
            'id' => $conversation->id,
            'customer_id' => $conversation->customer_id,
            'name' => $customerName,
            'avatar' => Str::upper(Str::substr($customerName, 0, 2)),
            'label' => 'Direct Message',
            'subtitle' => $conversation->customer?->created_at
                ? 'Customer since ' . $conversation->customer->created_at->format('M Y')
                : 'Customer support',
            'time' => $conversation->last_message_at?->diffForHumans() ?? 'No messages',
            'last_message_at' => $conversation->last_message_at?->toISOString(),
            'last_message_at_human' => $conversation->last_message_at?->diffForHumans() ?? 'No messages',
            'last_message_content' => $lastMessage?->content ?? 'No messages yet',
            'latest_message_id' => $lastMessage?->id,
            'preview' => $lastMessage?->content ?? 'No messages yet',
            'unread' => (int) ($conversation->unread_count ?? 0) > 0,
            'unread_count' => (int) ($conversation->unread_count ?? 0),
        ];
    }

    private function serializeMessage(Message $message): array
    {
        return [
            'id' => $message->id,
            'conversation_id' => $message->conversation_id,
            'sender_id' => $message->sender_id,
            'content' => $message->content,
            'is_read' => (bool) $message->is_read,
            'created_at' => $message->created_at?->toISOString(),
            'updated_at' => $message->updated_at?->toISOString(),
            'sender' => [
                'user_id' => $message->sender?->user_id,
                'role' => $message->sender?->role,
                'detail' => $message->sender?->detail ? [
                    'name' => $message->sender->detail->name,
                    'email' => $message->sender->detail->email,
                ] : null,
            ],
        ];
    }
}
