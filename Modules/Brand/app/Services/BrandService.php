<?php

namespace Modules\Brand\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\ContentMediaService;
use App\Services\Media\MediaService;
use Modules\Brand\Models\Brand;

class BrandService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private MediaService $media, private ContentMediaService $contentMedia) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Brand $brand = null, ?Admin $actor = null): Brand
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $brand, $actor): Brand {
            $brand = $brand === null ? new Brand : Brand::query()->lockForUpdate()->findOrFail($brand->id);
            $extraFields = $this->values->prepare($this->features->form('Brand', 'brand')['fields'], $data, $brand->extra_fields ?? [], 'brand/features', $files, $actor);
            $brand->fill([
                'name' => $data['name'],
                'sort_order' => $data['sort_order'] ?? null,
                'is_active' => $data['is_active'] ?? $brand->is_active ?? true,
                ...$this->contentMedia->prepare($brand, $data, $this->contentMedia->definition(), $actor, true),
                'short_description' => $data['short_description'] ?? null,
                'link' => $data['link'] ?? null,
                'extra_fields' => $extraFields,
            ])->save();

            $this->contentMedia->syncUsage($brand, 'image', 'Marka / '.$brand->name, 'admin.brand.edit');
            $this->values->syncMediaUsages($this->features->form('Brand', 'brand')['fields'], $brand, $this->media, 'Marka / '.$brand->name, 'admin.brand.edit');

            return $brand;
        });
    }

    public function delete(Brand $brand): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($brand): void {
            $brand = Brand::query()->lockForUpdate()->findOrFail($brand->id);
            foreach ($this->features->form('Brand', 'brand')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    $files->replace(null, $brand->extra_fields[$key] ?? null, 'brand/features', true);
                }
            }
            $this->media->detachUsages($brand);
            $brand->delete();
        });
    }
}
