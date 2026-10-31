<?php

namespace Modules\Brand\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Settings\Language;
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
            $isNew = $brand === null;
            $brand = $isNew ? new Brand : Brand::query()->lockForUpdate()->findOrFail($brand->id);
            $language = Language::query()->where('is_active', true)->findOrFail($data['lang_id']);
            if ($isNew || $language->code === BrandLanguageService::PrimaryCode) {
                $brand->fill(['sort_order' => $data['sort_order'] ?? $brand->sort_order, 'is_active' => $data['is_active'] ?? $brand->is_active ?? true,
                    'link' => $data['link'] ?? null, ...$this->contentMedia->prepare($brand, $data, $this->contentMedia->definition(), $actor)])->save();
            }
            $translation = $brand->translations()->firstOrNew(['brand_id' => $brand->id, 'lang_id' => $language->id]);
            $extraFields = $this->values->prepare($this->features->form('Brand', 'brand')['fields'], $data, $translation->extra_fields ?? [], 'brand/'.$brand->id.'/translations/'.$language->id.'/features', $files, $actor);
            $translation->fill(['name' => $data['name'], 'short_description' => $data['short_description'] ?? null, 'extra_fields' => $extraFields])->save();
            if ($language->code === BrandLanguageService::PrimaryCode) {
                $this->contentMedia->syncUsage($brand, 'image', 'Marka / '.$translation->name, 'admin.brand.edit');
            }
            $this->values->syncMediaUsages($this->features->form('Brand', 'brand')['fields'], $translation, $this->media, 'Marka / '.$translation->name, 'admin.brand.edit');

            return $brand;
        });
    }

    public function delete(Brand $brand): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($brand): void {
            $brand = Brand::query()->lockForUpdate()->findOrFail($brand->id);
            foreach ($brand->translations as $translation) {
                foreach ($this->features->form('Brand', 'brand')['fields'] as $key => $definition) {
                    if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                        $files->replace(null, $translation->extra_fields[$key] ?? null, 'brand/'.$brand->id.'/translations/'.$translation->lang_id.'/features', true);
                    }
                }
                $this->media->detachUsages($translation);
            }
            $this->media->detachUsages($brand);
            $brand->delete();
        });
    }
}
