<?php

namespace Modules\Services\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\HtmlSanitizer;
use App\Services\Media\ContentMediaService;
use App\Services\Media\MediaService;
use Modules\Services\Models\Service;

class ServiceService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private HtmlSanitizer $sanitizer, private MediaService $media, private ContentMediaService $contentMedia) {}

    /** @param array<string, mixed> $data */
    public function create(array $data, ?Admin $actor = null): Service
    {
        return $this->save($data, null, $actor);
    }

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Service $service = null, ?Admin $actor = null): Service
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $service, $actor): Service {
            $service = $service === null ? new Service : Service::query()->lockForUpdate()->findOrFail($service->id);
            $service->fill([
                'service_category_id' => $data['service_category_id'] ?? null,
                'sort_order' => $data['sort_order'] ?? null,
                'is_active' => (bool) $data['is_active'],
                ...$this->contentMedia->prepare($service, $data, $this->contentMedia->definition(), $actor),
                'icon' => $data['icon'] ?? null,
            ])->save();

            $language = Language::query()->where('is_active', true)->findOrFail($data['lang_id']);
            $translation = $service->translations()->firstOrNew(['lang_id' => $language->id]);
            $translation->fill([
                'title' => $data['title'], 'slug' => $data['slug'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'content' => isset($data['content']) ? $this->sanitizer->sanitize($data['content']) : null,
                'meta_title' => $data['meta_title'] ?? null, 'meta_keywords' => $data['meta_keywords'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
            ]);
            $translation->extra_fields = $this->values->prepare($this->features->form('Services', 'service')['fields'], $data, $translation->extra_fields ?? [], 'services/features', $files, $actor);
            $translation->save();
            $this->contentMedia->syncUsage($service, 'image', 'Hizmet / '.$translation->title, 'admin.services.edit');
            $this->values->syncMediaUsages($this->features->form('Services', 'service')['fields'], $translation, $this->media, 'Hizmet / '.$translation->title, 'admin.services.translations.edit');

            return $service;
        });
    }

    public function delete(Service $service): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($service): void {
            $service = Service::query()->lockForUpdate()->findOrFail($service->id);
            foreach ($service->translations as $translation) {
                foreach ($this->features->form('Services', 'service')['fields'] as $key => $definition) {
                    if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                        $files->replace(null, $translation->extra_fields[$key] ?? null, 'services/features', true);
                    }
                }
                $this->media->detachUsages($translation);
            }
            $this->media->detachUsages($service);
            $service->delete();
        });
    }
}
