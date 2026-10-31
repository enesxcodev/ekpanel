<?php

namespace Modules\Documents\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveDocumentCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('category') ? 'documents.categories.update' : 'documents.categories.create') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(ModuleFeatures $features, FeatureValues $values): array
    {
        $category = $this->route('category');
        $languageId = $this->input('lang_id');
        $translation = $category && is_numeric($languageId)
            ? $category->translations()->where('lang_id', $languageId)->first()
            : null;

        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)],
            'name' => ['required', 'string', 'max:255', Rule::unique('document_category_translations', 'name')->where(fn ($query) => $query->where('lang_id', $languageId))->ignore($translation?->id)],
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            ...$values->rules($features->form('Documents', 'category')['fields'], $this->all(), $translation?->extra_fields ?? []),
        ];
    }

    /** @return array<string, string|array<string, string>> */
    public function messages(): array
    {
        return [
            'name.required' => 'Kategori adını girin.',
            'name.unique' => 'Bu kategori adı bu dilde zaten kullanılıyor.',
        ] + (new FieldRuleBuilder)->messages();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['name' => 'Kategori adı', 'icon' => 'İkon']
            + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Documents', 'category')['fields']);
    }
}
