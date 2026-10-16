<?php

namespace Modules\Sector\Services;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use Modules\Sector\Models\Sector;

class SectorService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Sector $sector = null): Sector
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $sector): Sector {
            $sector = $sector === null ? new Sector : Sector::query()->lockForUpdate()->findOrFail($sector->id);
            $extraFields = $this->values->prepare($this->features->form('Sector', 'sector')['fields'], $data, $sector->extra_fields ?? [], 'sector/features', $files);
            $sector->fill([
                'title' => $data['title'],
                'short_description' => $data['short_description'] ?? null,
                'content' => $data['content'] ?? null,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_keywords' => $data['meta_keywords'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'is_active' => (bool) $data['is_active'],
                'image' => $files->replace($data['image'] ?? null, $sector->image, 'sector/images', (bool) ($data['remove_image'] ?? false)),
                'extra_fields' => $extraFields,
            ])->save();

            return $sector;
        });
    }

    public function delete(Sector $sector): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($sector): void {
            $sector = Sector::query()->lockForUpdate()->findOrFail($sector->id);
            $files->replace(null, $sector->image, 'sector/images', true);
            foreach ($this->features->form('Sector', 'sector')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile()) {
                    $files->replace(null, $sector->extra_fields[$key] ?? null, 'sector/features', true);
                }
            }
            $sector->delete();
        });
    }
}
