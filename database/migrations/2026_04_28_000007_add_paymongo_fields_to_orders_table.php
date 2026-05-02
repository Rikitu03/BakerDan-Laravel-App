<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->string('payment_provider')->nullable()->after('payment_method');
            $table->string('payment_reference')->nullable()->after('payment_provider');
            $table->string('payment_session_id')->nullable()->after('payment_reference');
            $table->text('payment_checkout_url')->nullable()->after('payment_session_id');
            $table->timestamp('payment_paid_at')->nullable()->after('payment_checkout_url');
            $table->json('payment_metadata')->nullable()->after('payment_paid_at');

            $table->index('payment_session_id');
            $table->index('payment_reference');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table): void {
            $table->dropIndex(['payment_session_id']);
            $table->dropIndex(['payment_reference']);
            $table->dropColumn([
                'payment_provider',
                'payment_reference',
                'payment_session_id',
                'payment_checkout_url',
                'payment_paid_at',
                'payment_metadata',
            ]);
        });
    }
};
