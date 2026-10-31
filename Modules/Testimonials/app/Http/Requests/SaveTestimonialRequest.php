<?php

namespace Modules\Testimonials\Http\Requests;

use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\ContentMediaService;
use Illuminate\Foundation\Http\FormRequest;

class SaveTestimonialRequest extends FormRequest
{
    public function rules(ModuleFeatures $features, FeatureValues $values, ContentMediaService $media): array
    {
        $testimonial = $this->route('testimonial');
        $language = Language::query()->where('is_active', true)->find($this->input('lang_id'));
        $sharedRules = ! $testimonial || $language?->code === 'tr';
        $existingExtraFields = $testimonial?->translations()->where('lang_id', $this->input('lang_id'))->first()?->extra_fields ?? [];

        return [
            'lang_id' => ['required', 'integer', 'exists:languages,id'],
            'customer_name' => ['required', 'string', 'max:255'],
            'company' => ['nullable', 'string', 'max:255'],
            'job_title' => ['nullable', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:65535'],
            ...($sharedRules ? $media->rules($media->definition()) : []),
            ...($sharedRules ? ['is_active' => ['sometimes', 'boolean'], 'sort_order' => ['sometimes', 'integer', 'between:0,65535']] : []),
            ...$values->rules($features->form('Testimonials', 'testimonial')['fields'], $this->all(), $existingExtraFields),
        ];
    }

    public function authorize(): bool
    {
        return ($this->user('admin')?->can($this->route('testimonial') ? 'testimonials.update' : 'testimonials.create') ?? false)
            && ($this->route('testimonial') !== null || Language::query()->whereKey($this->input('lang_id'))->where('code', 'tr')->where('is_active', true)->exists());
    }

    /** @return array<string, string|array<string, string>> */
    public function messages(): array
    {
        return ['customer_name.required' => 'Müşteri adını girin.', 'content.required' => 'Yorum alanını doldurun.', 'image.prohibited_if' => 'Yeni görsel yükleme ve mevcut görseli kaldırma işlemlerinden birini seçin.']
            + (new FieldRuleBuilder)->messages();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'customer_name' => 'Müşteri adı',
            'company' => 'Firma',
            'job_title' => 'Unvan',
            'content' => 'Yorum',
            'image_media_id' => 'Görsel',
            'remove_image' => 'Görseli kaldır',
            'is_active' => 'Yayın onayı',
        ] + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Testimonials', 'testimonial')['fields']);
    }
}
