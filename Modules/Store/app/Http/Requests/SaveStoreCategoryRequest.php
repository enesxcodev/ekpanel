<?php

namespace Modules\Store\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveStoreCategoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => $this->input('slug') ?: Str::slug(is_string($this->input('name')) ? $this->input('name') : '', language: 'tr')]);
    }

    public function rules(): array
    {
        return ['name' => ['required', 'string', 'max:255'], 'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', Rule::unique('store_categories', 'slug')->ignore($this->route('category')?->id)], 'sort_order' => ['required', 'integer', 'min:0', 'max:65535'], 'is_active' => ['required', 'boolean']];
    }

    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('category') ? 'store.categories.update' : 'store.categories.create') ?? false;
    }
}
