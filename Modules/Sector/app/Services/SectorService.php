<?php

namespace Modules\Sector\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\HtmlSanitizer;
use App\Services\Media\ContentMediaService;
use App\Services\Media\MediaService;
use Modules\Sector\Models\Sector;

class SectorService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private HtmlSanitizer $sanitizer, private MediaService $media, private ContentMediaService $contentMedia) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Sector $sector = null, ?Admin $actor = null): Sector
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $sector, $actor): Sector {
            $sector = $sector === null ? new Sector : Sector::query()->lockForUpdate()->findOrFail($sector->id);
            $extraFields = $this->values->prepare($this->features->form('Sector', 'sector')['fields'], $data, $sector->extra_fields ?? [], 'sector/features', $files, $actor);
            $sector->fill([
                'title' => $data['title'],
                'slug' => $data['slug'] ?? null,
                'sort_order' => $data['sort_order'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'content' => isset($data['content']) ? $this->sanitizer->sanitize($data['content']) : null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'is_active' => (bool) $data['is_active'],
                ...$this->contentMedia->prepare($sector, $data, $this->contentMedia->definition(), $actor),
                'extra_fields' => $extraFields,
            ])->save();

            $this->contentMedia->syncUsage($sector, 'image', 'Sektör / '.$sector->title, 'admin.sector.edit');
            $this->values->syncMediaUsages($this->features->form('Sector', 'sector')['fields'], $sector, $this->media, 'Sektör / '.$sector->title, 'admin.sector.edit');

            return $sector;
        });
    }

    public function delete(Sector $sector): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($sector): void {
            $sector = Sector::query()->lockForUpdate()->findOrFail($sector->id);
            foreach ($this->features->form('Sector', 'sector')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    $files->replace(null, $sector->extra_fields[$key] ?? null, 'sector/features', true);
                }
            }
            $this->media->detachUsages($sector);
            $sector->delete();
        });
    }
}
