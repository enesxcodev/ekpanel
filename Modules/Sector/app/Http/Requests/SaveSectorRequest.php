<?php

namespace Modules\Sector\Http\Requests;

use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\ContentMediaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Sector\Services\SectorLanguageService;

class SaveSectorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('sector') ? 'sector.update' : 'sector.create') ?? false;
    }

    public function rules(ModuleFeatures $features, FeatureValues $values, ContentMediaService $media): array
    {
        $sector = $this->route('sector');
        $isPrimary = $this->input('lang_id') && Language::query()->whereKey($this->input('lang_id'))->where('code', SectorLanguageService::PrimaryCode)->exists();

        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)],
            'slug' => ['nullable', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('sector_translations', 'slug')->where('lang_id', $this->input('lang_id'))->ignore($sector?->translations()->where('lang_id', $this->input('lang_id'))->value('id'))],
            'sort_order' => [$isPrimary ? 'nullable' : 'prohibited', 'integer', 'min:0', 'max:65535'],
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string', 'max:65535'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:1000'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            ...($isPrimary ? $media->rules($media->definition()) : ['image_media_id' => ['prohibited'], 'remove_image' => ['prohibited']]),
            'is_active' => [$isPrimary ? 'required' : 'prohibited', 'boolean'],
            ...$values->rules($features->form('Sector', 'sector')['fields'], $this->all(), $sector?->translations()->where('lang_id', $this->input('lang_id'))->first()?->extra_fields ?? []),
        ];
    }

    public function messages(): array
    {
        return ['image.prohibited_if' => 'Yeni görsel yükleme ve mevcut görseli kaldırma işlemlerinden birini seçin.'] + (new FieldRuleBuilder)->messages();
    }

    public function attributes(): array
    {
        return ['lang_id' => 'Dil', 'slug' => 'Kısa bağlantı', 'sort_order' => 'Sıralama', 'title' => 'Başlık', 'short_description' => 'Kısa içerik', 'content' => 'İçerik', 'meta_title' => 'Meta başlık', 'meta_keywords' => 'Meta anahtar', 'meta_description' => 'Meta açıklama', 'image_media_id' => 'Görsel', 'is_active' => 'Durum', 'remove_image' => 'Görseli kaldır']
            + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Sector', 'sector')['fields']);
    }
}
