<?php

namespace Modules\Services\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use Modules\Services\Models\ServiceCategory;

class ServiceCategoryService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private MediaService $media) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?ServiceCategory $category = null, ?Admin $actor = null): ServiceCategory
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $category, $actor): ServiceCategory {
            $category = $category === null
                ? new ServiceCategory
                : ServiceCategory::query()->lockForUpdate()->findOrFail($category->id);
            $category->fill(['name' => $data['name'], 'sort_order' => $data['sort_order'] ?? null, 'is_active' => $data['is_active'] ?? $category->is_active ?? true]);
            $category->save();

            $category->extra_fields = $this->values->prepare(
                $this->features->form('Services', 'category')['fields'],
                $data,
                $category->extra_fields ?? [],
                'services/categories/'.$category->id.'/features',
                $files,
                $actor,
            );
            $category->save();

            $this->values->syncMediaUsages($this->features->form('Services', 'category')['fields'], $category, $this->media, 'Hizmet kategorisi / '.$category->name, 'admin.services.categories.edit');

            return $category;
        });
    }

    public function delete(ServiceCategory $category): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($category): void {
            $category = ServiceCategory::query()->lockForUpdate()->findOrFail($category->id);
            foreach ($this->features->form('Services', 'category')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    $files->replace(null, $category->extra_fields[$key] ?? null, 'services/categories/'.$category->id.'/features', true);
                }
            }
            $this->media->detachUsages($category);
            $category->delete();
        });
    }
}
