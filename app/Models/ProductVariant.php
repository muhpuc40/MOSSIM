<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ProductVariant extends Model
{
    use HasUuids;

    protected $table = 'product_variants';

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'color_id',
        'size_id',
        'sku',
        'is_default',
        'stock_qty',
        'is_active',
        'updated_by',
        'updated_at',
    ];

    protected $casts = [
        'is_default'  => 'boolean',
        'is_active'   => 'boolean',
        'stock_qty'   => 'integer',
        'updated_at'  => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function color(): BelongsTo
    {
        return $this->belongsTo(ProductColor::class, 'color_id');
    }

    public function size(): BelongsTo
    {
        return $this->belongsTo(ProductSize::class, 'size_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'updated_by');
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class, 'variant_id');
    }

    public function prices(): HasMany
    {
        return $this->hasMany(ProductPrice::class, 'variant_id');
    }

    // Single default price row — used in lean API list
    public function defaultPrice(): HasOne
    {
        return $this->hasOne(ProductPrice::class, 'variant_id')
                    ->where('is_default', true);
    }

    public function stockLogs(): HasMany
    {
        return $this->hasMany(StockLog::class, 'variant_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'variant_id');
    }
}