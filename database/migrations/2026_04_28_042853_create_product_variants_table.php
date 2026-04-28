<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_variants', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->foreignUuid('color_id')->constrained('product_colors')->restrictOnDelete();
            $table->foreignUuid('size_id')->constrained('product_sizes')->restrictOnDelete();
            $table->string('sku', 30)->unique();
            $table->boolean('is_default')->default(false);
            $table->integer('stock_qty')->default(0);
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('updated_by')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->timestamp('updated_at')->nullable();

            $table->index('product_id');
            $table->index(['product_id', 'is_active']);
        });
        // Note: one-default-per-product is enforced at the service layer via transaction
    }

    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};