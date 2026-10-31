<?php

namespace Modules\Brand\Http\Requests;

use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\ContentMediaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Brand\Services\BrandLanguageService;

class SaveBrandRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('brand') ? 'brand.update' : 'brand.create') ?? false;
    }

    public function rules(ModuleFeatures $features, FeatureValues $values, ContentMediaService $media): array
    {
        $brand = $this->route('brand');

        $language = is_numeric($this->input('lang_id')) ? Language::query()->find((int) $this->input('lang_id')) : null;
        if ($brand && $language?->code !== BrandLanguageService::PrimaryCode) {
            foreach (['sort_order', 'is_active', 'image_media_id', 'remove_image', 'image'] as $sharedField) {
                $this->request->remove($sharedField);
                $this->files->remove($sharedField);
            }
        }
        $existingExtraFields = $brand && $language ? $brand->translations()->where('lang_id', $language->id)->value('extra_fields') : [];

        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true), function (string $attribute, mixed $value, \Closure $fail) use ($brand): void {
                if (! $brand && Language::query()->whereKey($value)->value('code') !== BrandLanguageService::PrimaryCode) {
                    $fail('Yeni markalar Türkçe oluşturulmalıdır.');
                }
            }],
            'sort_order' => $language?->code === BrandLanguageService::PrimaryCode || ! $brand ? ['nullable', 'integer', 'min:0', 'max:65535'] : ['prohibited'],
            'is_active' => $language?->code === BrandLanguageService::PrimaryCode || ! $brand ? ['sometimes', 'boolean'] : ['prohibited'],
            'name' => ['required', 'string', 'max:255'],
            ...($language?->code === BrandLanguageService::PrimaryCode || ! $brand ? $media->rules($media->definition(), ! ($brand && ! $brand->image_media_id && $brand->image)) : ['image_media_id' => ['prohibited'], 'remove_image' => ['prohibited']]),
            'short_description' => ['nullable', 'string', 'max:500'],
            'link' => $language?->code === BrandLanguageService::PrimaryCode || ! $brand ? ['nullable', 'string', 'url:http,https', 'max:2048'] : ['prohibited'],
            ...$values->rules($features->form('Brand', 'brand')['fields'], $this->all(), is_array($existingExtraFields) ? $existingExtraFields : []),
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
