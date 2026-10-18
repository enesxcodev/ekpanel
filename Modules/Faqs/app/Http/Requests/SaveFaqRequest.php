<?php

namespace Modules\Faqs\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;

class SaveFaqRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('faq') ? 'faqs.update' : 'faqs.create') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(ModuleFeatures $features, FeatureValues $values): array
    {
        $faq = $this->route('faq');

        return [
            'name' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:65535'],
            'category_id' => ['nullable', 'integer', 'exists:faq_categories,id'],
            ...$values->rules($features->form('Faqs', 'faq')['fields'], $this->all(), $faq?->extra_fields ?? []),
        ];
    }

    /** @return array<string, string|array<string, string>> */
    public function messages(): array
    {
        return [
            'name.required' => 'Soruyu girin.',
            'content.required' => 'Yanıtı girin.',
        ] + (new FieldRuleBuilder)->messages();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'name' => 'Soru',
            'content' => 'Yanıt',
            'category_id' => 'Kategori',
        ] + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Faqs', 'faq')['fields']);
    }
}
