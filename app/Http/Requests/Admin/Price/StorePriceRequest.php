<?php

namespace App\Http\Requests\Admin\Price;

use Illuminate\Foundation\Http\FormRequest;

class StorePriceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'currency_id'    => ['required', 'uuid', 'exists:currencies,id'],
            'actual_price'   => ['required', 'numeric', 'min:0'],
            'discount_type'  => ['nullable', 'in:percent,flat'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
            'is_default'     => ['boolean'],
        ];
    }
}