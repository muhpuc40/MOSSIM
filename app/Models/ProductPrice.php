<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProductPrice extends Model
{
    use HasUuids;

    protected $table    = 'product_prices';
    public    $timestamps = false;

    protected $fillable = [
        'variant_id',
        'currency_id',
        'actual_price',
        'discount_type',   // percent | flat | null
        'discount_value',
        'is_default',
    ];

    protected $casts = [
        'actual_price'  => 'decimal:2',
        'discount_value'=> 'decimal:2',
        'current_price' => 'decimal:2',  // generated column — read only
        'is_default'    => 'boolean',
    ];

    // current_price is a MySQL generated column — never write to it
    protected $guarded = ['current_price'];

    public function variant(): BelongsTo
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function currency(): BelongsTo
    {
        return $this->belongsTo(Currency::class, 'currency_id');
    }
}