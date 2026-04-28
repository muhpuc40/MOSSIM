<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // gate/policy handled by middleware
    }

    public function rules(): array
    {
        return [
            'product_code'  => ['required', 'string', 'max:20', 'unique:products,product_code'],
            'name'          => ['required', 'string', 'max:200'],
            'type'          => ['required', 'in:man,women,kids,unisex'],
            'description'   => ['required', 'string'],
            'is_active'     => ['boolean'],

            // Colors (at least one required)
            'colors'                => ['required', 'array', 'min:1'],
            'colors.*.color_name'   => ['required', 'string', 'max:50'],
            'colors.*.color_hex'    => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],

            // Sizes (at least one required)
            'sizes'                 => ['required', 'array', 'min:1'],
            'sizes.*.size_label'    => ['required', 'string', 'max:20'],
            'sizes.*.sort_order'    => ['integer', 'min:0'],
        ];
    }
}