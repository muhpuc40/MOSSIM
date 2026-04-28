<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class Product extends Model
{
    use HasUuids;

    protected $table = 'products';

    protected $fillable = [
        'product_code',
        'name',
        'type',
        'description',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    public function colors(): HasMany
    {
        return $this->hasMany(ProductColor::class, 'product_id');
    }

    public function sizes(): HasMany
    {
        return $this->hasMany(ProductSize::class, 'product_id');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'product_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'product_id');
    }

    // Access prices across all variants
    public function prices(): HasManyThrough
    {
        return $this->hasManyThrough(ProductPrice::class, ProductVariant::class, 'product_id', 'variant_id');
    }
}