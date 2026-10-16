<?php

namespace Modules\Sector\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;

class SaveSectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('sector') ? 'sector.update' : 'sector.create') ?? false;
    }

    public function rules(ModuleFeatures $features, FeatureValues $values): array
    {
        $sector = $this->route('sector');

        return [
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string', 'max:65535'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:1000'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'prohibited_if:remove_image,1', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:4096'],
            'remove_image' => ['sometimes', 'boolean'],
            'is_active' => ['required', 'boolean'],
            ...$values->rules($features->form('Sector', 'sector')['fields'], $this->all(), $sector?->extra_fields ?? []),
        ];
    }

    public function messages(): array
    {
        return ['image.prohibited_if' => 'Yeni görsel yükleme ve mevcut görseli kaldırma işlemlerinden birini seçin.']
            + (new FieldRuleBuilder)->messages();
    }

    public function attributes(): array
    {
        return ['title' => 'Başlık', 'short_description' => 'Kısa içerik', 'content' => 'İçerik', 'meta_title' => 'Meta başlık', 'meta_keywords' => 'Meta anahtar', 'meta_description' => 'Meta açıklama', 'image' => 'Görsel', 'is_active' => 'Durum', 'remove_image' => 'Görseli kaldır']
            + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Sector', 'sector')['fields']);
    }
}
