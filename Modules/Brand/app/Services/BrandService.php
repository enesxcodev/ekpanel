<?php

namespace Modules\Brand\Services;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use Modules\Brand\Models\Brand;

class BrandService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Brand $brand = null): Brand
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $brand): Brand {
            $brand = $brand === null ? new Brand : Brand::query()->lockForUpdate()->findOrFail($brand->id);
            $extraFields = $this->values->prepare($this->features->form('Brand', 'brand')['fields'], $data, $brand->extra_fields ?? [], 'brand/features', $files);
            $brand->fill([
                'name' => $data['name'],
                'image' => $files->replace($data['image'] ?? null, $brand->image, 'brand/images'),
                'short_description' => $data['short_description'] ?? null,
                'link' => $data['link'] ?? null,
                'extra_fields' => $extraFields,
            ])->save();

            return $brand;
        });
    }

    public function delete(Brand $brand): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($brand): void {
            $brand = Brand::query()->lockForUpdate()->findOrFail($brand->id);
            $files->replace(null, $brand->image, 'brand/images', true);
            foreach ($this->features->form('Brand', 'brand')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile()) {
                    $files->replace(null, $brand->extra_fields[$key] ?? null, 'brand/features', true);
                }
            }
            $brand->delete();
        });
    }
}
