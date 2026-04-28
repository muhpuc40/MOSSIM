<?php

namespace App\Http\Requests\Admin\Coupon;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCouponRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $id = $this->route('coupon');

        return [
            'code'             => ['sometimes', 'string', 'max:30', Rule::unique('coupons', 'code')->ignore($id)],
            'discount_type'    => ['sometimes', 'in:pct,flat'],
            'discount_value'   => ['sometimes', 'numeric', 'min:0'],
            'min_order_amount' => ['nullable', 'numeric', 'min:0'],
            'max_uses'         => ['nullable', 'integer', 'min:1'],
            'valid_from'       => ['sometimes', 'date'],
            'valid_to'         => ['sometimes', 'date', 'after:valid_from'],
            'is_active'        => ['boolean'],
        ];
    }
}