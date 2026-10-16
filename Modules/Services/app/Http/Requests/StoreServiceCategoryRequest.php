<?php

namespace Modules\Services\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;

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

        return [
            'name' => ['required', 'string', 'max:255'],
            ...$values->rules($features->form('Services', 'category')['fields'], $this->all(), $category?->extra_fields ?? []),
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
        return ['name' => 'Kategori adı']
            + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Services', 'category')['fields']);
    }
}
