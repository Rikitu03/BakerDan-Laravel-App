<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('conversations', function (Blueprint $table): void {
            $table->index('last_message_at', 'conversations_last_message_at_index');
        });

        Schema::table('messages', function (Blueprint $table): void {
            $table->index(['conversation_id', 'id'], 'messages_conversation_id_id_index');
            $table->index(['conversation_id', 'sender_id', 'is_read'], 'messages_conversation_sender_read_index');
        });
    }

    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table): void {
            $table->dropIndex('messages_conversation_sender_read_index');
            $table->dropIndex('messages_conversation_id_id_index');
        });

        Schema::table('conversations', function (Blueprint $table): void {
            $table->dropIndex('conversations_last_message_at_index');
        });
    }
};
