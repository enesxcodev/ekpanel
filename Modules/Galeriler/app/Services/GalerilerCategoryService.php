<?php

namespace Modules\Galeriler\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Modules\Galeriler\Models\GalerilerCategory;

class GalerilerCategoryService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private MediaService $media) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?GalerilerCategory $category, Admin $actor): GalerilerCategory
    {
        return Cache::lock('galeriler-category-tree', 30)->block(5, function () use ($data, $category, $actor): GalerilerCategory {
            return $this->files->transaction(function (FieldFiles $files) use ($data, $category, $actor): GalerilerCategory {
                $category = $category ? GalerilerCategory::query()->lockForUpdate()->findOrFail($category->id) : new GalerilerCategory;
                if ($category->exists && isset($data['parent_id']) && in_array((int) $data['parent_id'], [$category->id, ...$category->descendantIds()], true)) {
                    throw ValidationException::withMessages(['parent_id' => 'Kategori kendisinin veya alt kategorisinin altına taşınamaz.']);
                }
                $category->fill(['parent_id' => $data['parent_id'] ?? null, 'name' => $data['name'], 'icon' => $data['icon'] ?? null, 'short_description' => $data['short_description'] ?? null])->save();
                $definitions = $this->features->form('Galeriler', 'category')['fields'];
                $category->extra_fields = $this->values->prepare($definitions, $data, $category->extra_fields ?? [], 'galeriler/categories/'.$category->id.'/features', $files, $actor);
                $category->save();
                $this->values->syncMediaUsages($definitions, $category, $this->media, 'Kategori / '.$category->name, 'admin.galeriler.categories.edit');

                return $category;
            });
        });
    }

    public function delete(GalerilerCategory $category): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($category): void {
            $category = GalerilerCategory::query()->lockForUpdate()->findOrFail($category->id);
            if ($category->items()->exists() || $category->children()->exists()) {
                throw ValidationException::withMessages(['category' => 'İçerik veya alt kategori bulunan kategori silinemez.']);
            }
            foreach ($this->features->form('Galeriler', 'category')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    $files->replace(null, $category->extra_fields[$key] ?? null, 'galeriler/categories/'.$category->id.'/features', true);
                }
            }
            $this->media->detachUsages($category);
            $category->delete();
        });
    }
}
