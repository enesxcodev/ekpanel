<?php

namespace Modules\Services\Services;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use Modules\Services\Models\ServiceCategory;

class ServiceCategoryService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?ServiceCategory $category = null): ServiceCategory
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $category): ServiceCategory {
            $category = $category === null
                ? new ServiceCategory
                : ServiceCategory::query()->lockForUpdate()->findOrFail($category->id);
            $category->name = $data['name'];
            $category->save();

            $category->extra_fields = $this->values->prepare(
                $this->features->form('Services', 'category')['fields'],
                $data,
                $category->extra_fields ?? [],
                'services/categories/'.$category->id.'/features',
                $files,
            );
            $category->save();

            return $category;
        });
    }

    public function delete(ServiceCategory $category): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($category): void {
            $category = ServiceCategory::query()->lockForUpdate()->findOrFail($category->id);
            foreach ($this->features->form('Services', 'category')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile()) {
                    $files->replace(null, $category->extra_fields[$key] ?? null, 'services/categories/'.$category->id.'/features', true);
                }
            }
            $category->delete();
        });
    }
}
