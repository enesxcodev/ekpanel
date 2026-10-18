<?php

namespace Modules\Testimonials\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\HtmlSanitizer;
use App\Services\Media\ContentMediaService;
use App\Services\Media\MediaService;
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
            $testimonial->fill([
                'customer_name' => $data['customer_name'],
                'company' => $data['company'] ?? null,
                'job_title' => $data['job_title'] ?? null,
                'content' => $this->sanitizer->sanitize($data['content']),
                ...$this->contentMedia->prepare($testimonial, $data, $this->contentMedia->definition(), $actor),
                'is_active' => (bool) ($data['is_active'] ?? false),
                'sort_order' => $data['sort_order'] ?? $testimonial->sort_order ?? 0,
            ])->save();
            $testimonial->extra_fields = $this->values->prepare(
                $this->features->form('Testimonials', 'testimonial')['fields'],
                $data,
                $testimonial->extra_fields ?? [],
                'testimonials/'.$testimonial->id.'/features',
                $files,
                $actor,
            );
            $testimonial->save();

            $this->contentMedia->syncUsage($testimonial, 'image', 'Müşteri yorumu / '.$testimonial->customer_name, 'admin.testimonials.edit');
            $this->values->syncMediaUsages($this->features->form('Testimonials', 'testimonial')['fields'], $testimonial, $this->media, 'Müşteri yorumu / '.$testimonial->customer_name, 'admin.testimonials.edit');

            return $testimonial;
        });
    }

    public function delete(Testimonial $testimonial): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($testimonial): void {
            $testimonial = Testimonial::query()->lockForUpdate()->findOrFail($testimonial->id);
            foreach ($this->features->form('Testimonials', 'testimonial')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    $files->replace(null, $testimonial->extra_fields[$key] ?? null, 'testimonials/'.$testimonial->id.'/features', true);
                }
            }
            $this->media->detachUsages($testimonial);
            $testimonial->delete();
        });
    }
}
