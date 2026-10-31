<?php

namespace Modules\Store\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Store\Services\StoreLanguageService;

class SaveStoreCategoryRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => $this->input('slug') ?: Str::slug(is_string($this->input('name')) ? $this->input('name') : '', language: 'tr'), 'lang_id' => $this->input('lang_id') ?: app(StoreLanguageService::class)->forAdmin(request()->query('lang'))?->id]);
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $languageId = $this->input('lang_id');

        return ['lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)], 'name' => ['required', 'string', 'max:255'], 'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', Rule::unique('store_category_translations', 'slug')->where('lang_id', $languageId)->ignore($category?->translations()->where('lang_id', $languageId)->value('id'))], 'sort_order' => ['required', 'integer', 'min:0', 'max:65535'], 'is_active' => ['required', 'boolean']];
    }

    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('category') ? 'store.categories.update' : 'store.categories.create') ?? false;
    }
}
