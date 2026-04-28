<?php

namespace App\Http\Requests\Admin\Product;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $productId = $this->route('product');

        return [
            'product_code'  => ['sometimes', 'string', 'max:20', "unique:products,product_code,{$productId}"],
            'name'          => ['sometimes', 'string', 'max:200'],
            'type'          => ['sometimes', 'in:man,women,kids,unisex'],
            'description'   => ['sometimes', 'string'],
            'is_active'     => ['boolean'],
        ];
    }
}