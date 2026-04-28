<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Coupon extends Model
{
    use HasUuids;

    protected $table    = 'coupons';
    public    $timestamps = false;

    protected $fillable = [
        'code',
        'discount_type',    // pct | flat
        'discount_value',
        'min_order_amount',
        'max_uses',
        'used_count',
        'valid_from',
        'valid_to',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'discount_value'   => 'decimal:2',
        'min_order_amount' => 'decimal:2',
        'is_active'        => 'boolean',
        'valid_from'       => 'datetime',
        'valid_to'         => 'datetime',
        'max_uses'         => 'integer',
        'used_count'       => 'integer',
    ];

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(AdminUser::class, 'created_by');
    }

    // ── Helper: check if coupon is currently usable ───────────────────────────
    public function isValid(): bool
    {
        if (! $this->is_active) return false;

        $now = now();

        if ($now->lt($this->valid_from) || $now->gt($this->valid_to)) return false;

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) return false;

        return true;
    }
}