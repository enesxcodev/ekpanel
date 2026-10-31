<?php

namespace Modules\Faqs\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveFaqCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('category') ? 'faqs.categories.update' : 'faqs.categories.create') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(ModuleFeatures $features, FeatureValues $values): array
    {
        $category = $this->route('category');
        $translation = $category?->translations()->where('lang_id', $this->input('lang_id'))->first();

        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)],
            'name' => ['required', 'string', 'max:255', Rule::unique('faq_category_translations', 'name')->where('lang_id', $this->input('lang_id'))->ignore($translation?->id)],
            ...$values->rules($features->form('Faqs', 'category')['fields'], $this->all(), $translation?->extra_fields ?? []),
        ];
    }

    /** @return array<string, string|array<string, string>> */
    public function messages(): array
    {
        return [
            'name.required' => 'Kategori adını girin.',
            'name.unique' => 'Bu kategori adı zaten kullanılıyor.',
        ] + (new FieldRuleBuilder)->messages();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['name' => 'Kategori adı']
            + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Faqs', 'category')['fields']);
    }
}
