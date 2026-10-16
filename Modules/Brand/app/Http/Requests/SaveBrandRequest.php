<?php

namespace Modules\Brand\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;

class SaveBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('brand') ? 'brand.update' : 'brand.create') ?? false;
    }

    public function rules(ModuleFeatures $features, FeatureValues $values): array
    {
        $brand = $this->route('brand');

        return [
            'name' => ['required', 'string', 'max:255'],
            'image' => [$brand?->image ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:4096'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'link' => ['nullable', 'string', 'url:http,https', 'max:2048'],
            ...$values->rules($features->form('Brand', 'brand')['fields'], $this->all(), $brand?->extra_fields ?? []),
        ];
    }

    public function messages(): array
    {
        return (new FieldRuleBuilder)->messages();
    }

    public function attributes(): array
    {
        return ['name' => 'Marka ismi', 'image' => 'Marka görseli', 'short_description' => 'Kısa açıklama', 'link' => 'Link']
            + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Brand', 'brand')['fields']);
    }
}
