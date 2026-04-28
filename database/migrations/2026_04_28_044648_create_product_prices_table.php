<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_prices', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('variant_id')->constrained('product_variants')->cascadeOnDelete();
            $table->foreignUuid('currency_id')->constrained('currencies')->restrictOnDelete();

            $table->decimal('actual_price', 12, 2);

            // null = no discount
            $table->enum('discount_type', ['percent', 'flat'])->nullable();
            $table->decimal('discount_value', 10, 2)->default(0);

            // MySQL generated column:
            //   percent → actual_price × (1 − discount_value / 100)
            //   flat    → actual_price − discount_value
            //   null    → actual_price
            $table->decimal('current_price', 12, 2)->storedAs(
                'ROUND(
                    CASE
                        WHEN discount_type = "percent" THEN actual_price * (1 - discount_value / 100)
                        WHEN discount_type = "flat"    THEN actual_price - discount_value
                        ELSE actual_price
                    END,
                2)'
            );

            // default price row shown on product listing
            $table->boolean('is_default')->default(false);

            $table->index(['variant_id', 'currency_id']);
            $table->index(['variant_id', 'is_default']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_prices');
    }
};