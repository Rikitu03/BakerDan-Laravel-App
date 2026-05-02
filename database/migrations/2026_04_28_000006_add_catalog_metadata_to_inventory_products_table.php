<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventory_products', function (Blueprint $table): void {
            $table->string('price_label')->nullable()->after('description');
            $table->text('sizes_available')->nullable()->after('price');
            $table->text('flavors_available')->nullable()->after('sizes_available');
            $table->string('image_source')->nullable()->after('image_url');
        });
    }

    public function down(): void
    {
        Schema::table('inventory_products', function (Blueprint $table): void {
            $table->dropColumn([
                'price_label',
                'sizes_available',
                'flavors_available',
                'image_source',
            ]);
        });
    }
};
