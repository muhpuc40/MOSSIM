<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;

class AdminUser extends Authenticatable
{
    use HasUuids;

    protected $table = 'admin_users';

    protected $fillable = [
        'name',
        'email',
        'password_hash',
        'role',
        'is_active',
    ];

    protected $hidden = [
        'password_hash',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // ─── Auth ─────────────────────────────────────────────────────────────────

    /**
     * Return the hashed password stored under the non-standard column name.
     * Laravel's session guard calls this when verifying credentials.
     */
    public function getAuthPassword(): string
    {
        return $this->password_hash;
    }

    // ─── Role helpers ─────────────────────────────────────────────────────────

    public function isMasterAdmin(): bool
    {
        return $this->role === 'master_admin';
    }

    public function isAtLeastAdmin(): bool
    {
        return in_array($this->role, ['master_admin', 'admin']);
    }

    // ─── Relationships ────────────────────────────────────────────────────────

    public function products(): HasMany
    {
        return $this->hasMany(Product::class, 'created_by');
    }

    public function coupons(): HasMany
    {
        return $this->hasMany(Coupon::class, 'created_by');
    }

    public function updatedVariants(): HasMany
    {
        return $this->hasMany(ProductVariant::class, 'updated_by');
    }
}
