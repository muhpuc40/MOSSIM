<?php

namespace App\Services;

use App\Models\Coupon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;

class CouponService
{
    public function list(array $filters = []): LengthAwarePaginator
    {
        return Coupon::query()
            ->when($filters['search'] ?? null, fn ($q, $v) =>
                $q->where('code', 'like', "%{$v}%")
            )
            ->when(isset($filters['is_active']), fn ($q) =>
                $q->where('is_active', (bool) $filters['is_active'])
            )
            ->latest('valid_from')
            ->paginate(20);
    }

    public function findOrFail(string $id): Coupon
    {
        return Coupon::findOrFail($id);
    }

    public function create(array $data): Coupon
    {
        return Coupon::create([...$data, 'created_by' => Auth::id()]);
    }

    public function update(Coupon $coupon, array $data): Coupon
    {
        $coupon->update($data);
        return $coupon->fresh();
    }

    public function delete(Coupon $coupon): void
    {
        $coupon->delete();
    }

    // ─── API: validate coupon code against an order amount ────────────────────

    public function validate(string $code, float $orderAmount): array
    {
        $coupon = Coupon::where('code', strtoupper($code))->first();

        if (!$coupon || !$coupon->isValid()) {
            return ['valid' => false, 'message' => 'Coupon is invalid or expired.'];
        }

        if ($orderAmount < $coupon->min_order_amount) {
            return [
                'valid'   => false,
                'message' => "Minimum order amount is {$coupon->min_order_amount}.",
            ];
        }

        $discount = $coupon->discount_type === 'pct'
            ? round($orderAmount * $coupon->discount_value / 100, 2)
            : min($coupon->discount_value, $orderAmount);

        return [
            'valid'           => true,
            'coupon'          => $coupon,
            'discount_amount' => $discount,
            'final_amount'    => round($orderAmount - $discount, 2),
        ];
    }
}