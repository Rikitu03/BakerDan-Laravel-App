<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * This migration will convert existing product.image_url values that are local paths
     * (starting with /images or images/ or not starting with http) to full cloud URLs
     * using config('app.aws_url'). It will also set image_source = 'cloud'.
     */
    public function up(): void
    {
        $root = rtrim(config('app.aws_url', ''), '/');

        if ($root === '') {
            // Nothing to do if aws_url not configured
            return;
        }

        $products = DB::table('products')
            ->whereNotNull('image_url')
            ->get(['id', 'image_url']);

        foreach ($products as $p) {
            $url = (string) $p->image_url;

            // Skip if already full URL
            if (preg_match('#^https?://#', $url)) {
                continue;
            }

            $path = ltrim($url, '/');
            $full = $root . '/' . $path;

            DB::table('products')
                ->where('id', $p->id)
                ->update(['image_url' => $full, 'image_source' => 'cloud']);
        }
    }

    public function down(): void
    {
        // no-op: don't revert automated URL updates
    }
};

