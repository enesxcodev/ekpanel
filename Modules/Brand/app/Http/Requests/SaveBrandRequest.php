<?php

namespace Modules\Brand\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\ContentMediaService;
use Illuminate\Foundation\Http\FormRequest;

class SaveBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('brand') ? 'brand.update' : 'brand.create') ?? false;
    }

    public function rules(ModuleFeatures $features, FeatureValues $values, ContentMediaService $media): array
    {
        $brand = $this->route('brand');

        return [
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['sometimes', 'boolean'],
            'name' => ['required', 'string', 'max:255'],
            ...$media->rules($media->definition(), ! ($brand && ! $brand->image_media_id && $brand->image)),
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
        return ['sort_order' => 'Sıralama', 'is_active' => 'Durum', 'name' => 'Marka ismi', 'image_media_id' => 'Marka görseli', 'short_description' => 'Kısa açıklama', 'link' => 'Link']
            + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Brand', 'brand')['fields']);
    }
}
