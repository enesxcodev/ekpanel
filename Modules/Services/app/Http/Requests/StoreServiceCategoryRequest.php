<?php

namespace Modules\Services\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('services.categories.create') ?? false;
    }

    /** @return array<string, list<string>> */
    public function rules(ModuleFeatures $features, FeatureValues $values): array
    {
        $category = $this->route('category');

        $translation = $category?->translations()->where('lang_id', $this->input('lang_id'))->first();

        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
            'name' => ['required', 'string', 'max:255'],
            ...$values->rules($features->form('Services', 'category')['fields'], $this->all(), $translation?->extra_fields ?? []),
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
            + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Services', 'category')['fields']);
    }
}
