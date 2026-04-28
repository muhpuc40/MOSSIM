<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('product_code', 20)->unique();   // e.g. MO1211
            $table->string('name', 200);
            $table->enum('type', ['man', 'women', 'kids', 'unisex']);
            $table->text('description');
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->constrained('admin_users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};