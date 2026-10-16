<?php

namespace Modules\Services\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('services.create') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(ModuleFeatures $features, FeatureValues $values): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'service_category_id' => ['nullable', 'integer', 'exists:service_categories,id'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string', 'max:65535'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:1000'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:4096'],
            'icon' => ['nullable', 'string', Rule::in(array_keys(config('services.icons')))],
            'is_active' => ['required', 'boolean'],
            ...$values->rules($features->form('Services', 'service')['fields'], $this->all(), []),
        ];
    }

    /** @return array<string, string|array<string, string>> */
    public function messages(): array
    {
        return [
            'title.required' => 'Hizmet başlığını girin.',
            'service_category_id.exists' => 'Geçerli bir hizmet kategorisi seçin.',
            'icon.in' => 'Listeden geçerli bir hizmet ikonu seçin.',
        ] + (new FieldRuleBuilder)->messages();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['title' => 'Hizmet başlık', 'service_category_id' => 'Kategori', 'short_description' => 'Hizmet kısa içerik', 'content' => 'Hizmet içerik', 'meta_title' => 'Meta başlık', 'meta_keywords' => 'Meta anahtar', 'meta_description' => 'Meta açıklama', 'image' => 'Hizmet resim', 'icon' => 'Hizmet ikon', 'is_active' => 'Hizmet durum']
            + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Services', 'service')['fields']);
    }
}
