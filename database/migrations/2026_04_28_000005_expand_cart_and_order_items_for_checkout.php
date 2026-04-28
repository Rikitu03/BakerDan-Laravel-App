<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cart_items', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->string('item_type')->default('catalog')->after('product_id');
            $table->decimal('unit_price', 10, 2)->nullable()->after('quantity');
            $table->string('product_name')->nullable()->after('unit_price');
            $table->text('description')->nullable()->after('product_name');
            $table->string('image_url')->nullable()->after('description');
            $table->text('design_description')->nullable()->after('image_url');
            $table->text('dedication_message')->nullable()->after('design_description');
            $table->foreign('product_id')->references('id')->on('inventory_products')->restrictOnDelete();
        });

        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->unsignedBigInteger('product_id')->nullable()->change();
            $table->string('item_type')->default('catalog')->after('product_id');
            $table->string('product_name')->nullable()->after('price');
            $table->text('description')->nullable()->after('product_name');
            $table->string('image_url')->nullable()->after('description');
            $table->text('design_description')->nullable()->after('image_url');
            $table->text('dedication_message')->nullable()->after('design_description');
            $table->foreign('product_id')->references('id')->on('inventory_products')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->dropColumn([
                'item_type',
                'product_name',
                'description',
                'image_url',
                'design_description',
                'dedication_message',
            ]);
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->foreign('product_id')->references('id')->on('inventory_products')->restrictOnDelete();
        });

        Schema::table('cart_items', function (Blueprint $table): void {
            $table->dropForeign(['product_id']);
            $table->dropColumn([
                'item_type',
                'unit_price',
                'product_name',
                'description',
                'image_url',
                'design_description',
                'dedication_message',
            ]);
            $table->unsignedBigInteger('product_id')->nullable(false)->change();
            $table->foreign('product_id')->references('id')->on('inventory_products')->restrictOnDelete();
        });
    }
};
