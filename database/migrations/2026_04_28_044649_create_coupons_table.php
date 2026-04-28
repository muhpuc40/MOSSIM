<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coupons', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('code', 30)->unique();
            $table->enum('discount_type', ['pct', 'flat']);
            $table->decimal('discount_value', 10, 2);
            $table->decimal('min_order_amount', 12, 2)->default(0);
            $table->integer('max_uses')->nullable();
            $table->integer('used_count')->default(0);
            $table->dateTime('valid_from');           // dateTime avoids MariaDB timestamp strict mode
            $table->dateTime('valid_to');
            $table->boolean('is_active')->default(true);
            $table->foreignUuid('created_by')->constrained('admin_users')->restrictOnDelete();

            $table->index('code');
            $table->index(['is_active', 'valid_from', 'valid_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coupons');
    }
};