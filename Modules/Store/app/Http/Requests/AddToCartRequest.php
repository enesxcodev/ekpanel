<?php

namespace Modules\Store\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    public function rules(): array
    {
        return ['product_id' => ['required', 'integer', 'exists:store_products,id'], 'variant_id' => ['nullable', 'integer', 'exists:store_variants,id'], 'quantity' => ['required', 'integer', 'min:1', 'max:100']];
    }

    public function authorize(): bool
    {
        return true;
    }
}
