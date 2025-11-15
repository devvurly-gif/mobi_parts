<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            // Drop the foreign key constraint first
            $table->dropForeign(['product_id']);
            // Make product_id nullable
            $table->foreignId('product_id')->nullable()->change();
            // Re-add the foreign key constraint with onDelete('set null') for nullable
            $table->foreign('product_id')->references('id')->on('products')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['product_id']);
            // Make product_id required again
            $table->foreignId('product_id')->nullable(false)->change();
            // Re-add the foreign key constraint with cascade
            $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
        });
    }
};
