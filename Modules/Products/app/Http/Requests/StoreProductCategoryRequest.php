<?php

namespace Modules\Products\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProductCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('products.categories.create') ?? false;
    }

    /** @return array<string, list<string>> */
    public function rules(ModuleFeatures $features, FeatureValues $values): array
    {
        $category = $this->route('category');
        $extraFields = $category?->translations()->where('lang_id', $this->input('lang_id'))->value('extra_fields');
        $existingExtraFields = is_string($extraFields) ? json_decode($extraFields, true) : [];

        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
            'name' => ['required', 'string', 'max:255'],
            ...$values->rules($features->form('Products', 'category')['fields'], $this->all(), is_array($existingExtraFields) ? $existingExtraFields : []),
        ];
    }

    /** @return array<string, string|array<string, string>> */
    public function messages(): array
    {
        return [
            'name.required' => 'Kategori adını girin.',
            'name.string' => 'Geçerli bir kategori adı girin.',
            'name.max' => 'Kategori adı en fazla 255 karakter olabilir.',
        ] + (new FieldRuleBuilder)->messages();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['sort_order' => 'Sıralama', 'is_active' => 'Durum', 'name' => 'Kategori adı']
            + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Products', 'category')['fields']);
    }
}
