<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_products', function (Blueprint $table): void {
            $table->index(['is_active', 'category'], 'inventory_products_active_category_index');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_products', function (Blueprint $table): void {
            $table->dropIndex('inventory_products_active_category_index');
        });
    }
};
