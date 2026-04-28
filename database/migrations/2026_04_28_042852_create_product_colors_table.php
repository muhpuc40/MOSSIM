<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_colors', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('product_id')->constrained('products')->cascadeOnDelete();
            $table->string('color_name', 50);
            $table->char('color_hex', 7)->nullable();   // e.g. #FF5733, null if no swatch
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_colors');
    }
};