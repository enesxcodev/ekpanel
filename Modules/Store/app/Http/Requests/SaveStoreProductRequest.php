<?php

namespace Modules\Store\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveStoreProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => $this->input('slug') ?: Str::slug((string) $this->input('title'), language: 'tr'), 'currency' => strtoupper((string) ($this->input('currency') ?: config('store.default_currency')))]);
    }

    public function rules(): array
    {
        $product = $this->route('product');

        return [
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('store_products', 'slug')->ignore($product?->id)],
            'sku' => ['required', 'string', 'max:100', Rule::unique('store_products', 'sku')->ignore($product?->id)],
            'store_category_id' => ['nullable', 'integer', 'exists:store_categories,id'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string', 'max:65535'],
            'price_minor' => ['required', 'integer', 'min:0'],
            'compare_at_price_minor' => ['nullable', 'integer', 'gte:price_minor'],
            'currency' => ['required', 'string', 'size:3', 'alpha'],
            'tracks_stock' => ['required', 'boolean'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_threshold' => ['required', 'integer', 'min:0'],
            'allow_backorders' => ['required', 'boolean'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
        ];
    }

    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('product') === null ? 'store.create' : 'store.update') ?? false;
    }
}
