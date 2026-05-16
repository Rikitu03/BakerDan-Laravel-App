<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class MessagingWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_message_creates_conversation_and_admin_can_fetch_summary(): void
    {
        config(['broadcasting.default' => 'reverb']);
        Event::fake([MessageSent::class]);

        $customer = $this->createUserWithDetail('customer', 'Customer One', 'customer_one', 'customer.one@example.test');
        $admin = $this->createUserWithDetail('admin', 'Admin One', 'admin_one', 'admin.one@example.test');

        $response = $this
            ->actingAs($customer)
            ->postJson('/api/messages', ['content' => 'Hello support']);

        $response
            ->assertOk()
            ->assertJsonPath('content', 'Hello support')
            ->assertJsonPath('sender_id', $customer->user_id);

        $this->assertDatabaseHas('conversations', [
            'customer_id' => $customer->user_id,
            'status' => 'active',
        ]);

        Event::assertDispatched(MessageSent::class, fn (MessageSent $event): bool => $event->message->id === $response->json('id'));

        $this
            ->actingAs($admin)
            ->getJson('/api/conversations')
            ->assertOk()
            ->assertJsonPath('0.customer_id', $customer->user_id)
            ->assertJsonPath('0.last_message_content', 'Hello support')
            ->assertJsonPath('0.unread_count', 1);
    }

    public function test_messages_do_not_broadcast_to_log_driver(): void
    {
        config(['broadcasting.default' => 'log']);
        Event::fake([MessageSent::class]);

        $customer = $this->createUserWithDetail('customer', 'Customer Log', 'customer_log', 'customer.log@example.test');

        $this
            ->actingAs($customer)
            ->postJson('/api/messages', ['content' => 'No log broadcast needed'])
            ->assertOk();

        Event::assertNotDispatched(MessageSent::class);
    }

    public function test_messages_endpoint_supports_incremental_fetches(): void
    {
        $customer = $this->createUserWithDetail('customer', 'Customer Two', 'customer_two', 'customer.two@example.test');
        $admin = $this->createUserWithDetail('admin', 'Admin Two', 'admin_two', 'admin.two@example.test');
        $conversation = Conversation::query()->create([
            'customer_id' => $customer->user_id,
            'status' => 'active',
            'last_message_at' => now(),
        ]);

        $first = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $customer->user_id,
            'content' => 'First message',
        ]);

        $second = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_id' => $admin->user_id,
            'content' => 'Latest reply',
        ]);

        $this
            ->actingAs($customer)
            ->getJson("/api/conversations/{$conversation->id}/messages?after_id={$first->id}")
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.id', $second->id)
            ->assertJsonPath('0.content', 'Latest reply');
    }

    public function test_customer_cannot_access_another_customer_conversation(): void
    {
        $owner = $this->createUserWithDetail('customer', 'Owner Customer', 'owner_customer', 'owner.customer@example.test');
        $intruder = $this->createUserWithDetail('customer', 'Other Customer', 'other_customer', 'other.customer@example.test');
        $conversation = Conversation::query()->create([
            'customer_id' => $owner->user_id,
            'status' => 'active',
            'last_message_at' => now(),
        ]);

        $this
            ->actingAs($intruder)
            ->getJson("/api/conversations/{$conversation->id}/messages")
            ->assertForbidden();
    }

    private function createUserWithDetail(string $role, string $name, string $username, string $email): User
    {
        $user = User::query()->create([
            'role' => $role,
            'is_active' => true,
        ]);

        UserDetail::query()->create([
            'user_id' => $user->user_id,
            'name' => $name,
            'username' => $username,
            'age' => 25,
            'email' => $email,
            'contact' => '+63 939 165 4377',
            'address' => 'Test Address',
            'password' => 'password123',
        ]);

        return $user->refresh();
    }
}
