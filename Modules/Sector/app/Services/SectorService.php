<?php

namespace Modules\Sector\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Settings\Language;
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
            $language = Language::query()->where('is_active', true)->findOrFail($data['lang_id']);
            $primary = $language->code === SectorLanguageService::PrimaryCode;
            if (! $sector->exists && ! $primary) {
                throw new \RuntimeException('Yeni sektör yalnızca Türkçe dilinde oluşturulabilir.');
            }
            if ($primary) {
                $sector->fill([
                    'sort_order' => $data['sort_order'] ?? null,
                    'is_active' => (bool) $data['is_active'],
                    ...$this->contentMedia->prepare($sector, $data, $this->contentMedia->definition(), $actor),
                ])->save();
                $this->contentMedia->syncUsage($sector, 'image', 'Sektör / '.$data['title'], 'admin.sector.edit');
            }
            $translation = $sector->translations()->firstOrNew(['lang_id' => $language->id]);
            $oldFields = $translation->extra_fields ?? [];
            $translation->fill([
                'title' => $data['title'], 'slug' => $data['slug'] ?? null,
                'short_description' => $data['short_description'] ?? null,
                'content' => isset($data['content']) ? $this->sanitizer->sanitize($data['content']) : null,
                'meta_title' => $data['meta_title'] ?? null, 'meta_keywords' => $data['meta_keywords'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
            ]);
            $translation->extra_fields = $this->values->prepare($this->features->form('Sector', 'sector')['fields'], $data, $oldFields, 'sector/'.$sector->id.'/translations/'.$language->id.'/features', $files, $actor);
            $translation->save();
            $this->values->syncMediaUsages($this->features->form('Sector', 'sector')['fields'], $translation, $this->media, 'Sektör / '.$translation->title, 'admin.sector.translations.edit');

            return $sector->setRelation('selectedTranslation', $translation);
        });
    }

    public function delete(Sector $sector): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($sector): void {
            $sector = Sector::query()->lockForUpdate()->findOrFail($sector->id);
            foreach ($sector->translations()->get() as $translation) {
                foreach ($this->features->form('Sector', 'sector')['fields'] as $key => $definition) {
                    if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                        $files->replace(null, $translation->extra_fields[$key] ?? null, 'sector/'.$sector->id.'/translations/'.$translation->lang_id.'/features', true);
                    }
                }
                $this->media->detachUsages($translation);
            }
            $this->media->detachUsages($sector);
            $sector->delete();
        });
    }
}
