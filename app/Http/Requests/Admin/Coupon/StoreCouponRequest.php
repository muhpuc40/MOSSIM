<?php

namespace App\Http\Requests\Admin\Coupon;

use Illuminate\Foundation\Http\FormRequest;

class StoreCouponRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'code'             => ['required', 'string', 'max:30', 'unique:coupons,code'],
            'discount_type'    => ['required', 'in:pct,flat'],
            'discount_value'   => ['required', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_uses'         => ['nullable', 'integer', 'min:1'],
            'valid_from'       => ['required', 'date'],
            'valid_to'         => ['required', 'date', 'after:valid_from'],
            'is_active'        => ['boolean'],
        ];
    }
}