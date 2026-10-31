<?php

namespace Modules\Testimonials\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\HtmlSanitizer;
use App\Services\Media\ContentMediaService;
use App\Services\Media\MediaService;
use Illuminate\Validation\ValidationException;
use Modules\Testimonials\Models\Testimonial;

class TestimonialService
{
    public function __construct(
        private ModuleFeatures $features,
        private FeatureValues $values,
        private FieldFiles $files,
        private HtmlSanitizer $sanitizer,
        private MediaService $media,
        private ContentMediaService $contentMedia,
    ) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Testimonial $testimonial = null, ?Admin $actor = null): Testimonial
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $testimonial, $actor): Testimonial {
            $testimonial = $testimonial === null
                ? new Testimonial
                : Testimonial::query()->lockForUpdate()->findOrFail($testimonial->id);
            $language = Language::query()->where('is_active', true)->findOrFail($data['lang_id']);
            if (! $testimonial->exists && $language->code !== 'tr') {
                throw ValidationException::withMessages(['lang_id' => 'Yeni yorumlar Türkçe olarak oluşturulmalıdır.']);
            }
            if (! $testimonial->exists || $language->code === 'tr') {
                $testimonial->fill([
                    ...$this->contentMedia->prepare($testimonial, $data, $this->contentMedia->definition(), $actor),
                    'is_active' => (bool) ($data['is_active'] ?? $testimonial->is_active ?? false),
                    'sort_order' => $data['sort_order'] ?? $testimonial->sort_order ?? 0,
                ])->save();
            }
            $translation = $testimonial->translations()->firstOrNew(['lang_id' => $language->id]);
            $translation->extra_fields = $this->values->prepare(
                $this->features->form('Testimonials', 'testimonial')['fields'],
                $data,
                $translation->extra_fields ?? [],
                'testimonials/'.$testimonial->id.'/features/'.$language->code,
                $files,
                $actor,
            );
            $translation->fill([
                'customer_name' => $data['customer_name'], 'company' => $data['company'] ?? null,
                'job_title' => $data['job_title'] ?? null, 'content' => $this->sanitizer->sanitize($data['content']),
            ])->save();

            if ($language->code === 'tr') {
                $this->contentMedia->syncUsage($testimonial, 'image', 'Müşteri yorumu / '.$translation->customer_name, 'admin.testimonials.edit');
            }
            $this->values->syncMediaUsages($this->features->form('Testimonials', 'testimonial')['fields'], $translation, $this->media, 'Müşteri yorumu / '.$translation->customer_name, 'admin.testimonials.translation.edit');

            return $testimonial;
        });
    }

    public function delete(Testimonial $testimonial): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($testimonial): void {
            $testimonial = Testimonial::query()->lockForUpdate()->findOrFail($testimonial->id);
            $testimonial->load('translations.language');
            foreach ($testimonial->translations as $translation) {
                foreach ($this->features->form('Testimonials', 'testimonial')['fields'] as $key => $definition) {
                    if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                        $files->replace(null, $translation->extra_fields[$key] ?? null, 'testimonials/'.$testimonial->id.'/features/'.$translation->language?->code, true);
                    }
                }
                $this->media->detachUsages($translation);
            }
            $this->media->detachUsages($testimonial);
            $testimonial->delete();
        });
    }
}
