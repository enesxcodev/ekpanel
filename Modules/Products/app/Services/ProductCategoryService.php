<?php

namespace Modules\Products\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use Modules\Products\Models\ProductCategory;

class ProductCategoryService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private MediaService $media) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?ProductCategory $category = null, ?Admin $actor = null): ProductCategory
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $category, $actor): ProductCategory {
            $category = $category === null
                ? new ProductCategory
                : ProductCategory::query()->lockForUpdate()->findOrFail($category->id);
            $category->fill(['name' => $data['name'], 'sort_order' => $data['sort_order'] ?? 0, 'is_active' => $data['is_active'] ?? $category->is_active ?? true]);
            $category->save();

            $category->extra_fields = $this->values->prepare(
                $this->features->form('Products', 'category')['fields'],
                $data,
                $category->extra_fields ?? [],
                'products/categories/'.$category->id.'/features',
                $files,
                $actor,
            );
            $category->save();

            $this->values->syncMediaUsages($this->features->form('Products', 'category')['fields'], $category, $this->media, 'Ürün kategorisi / '.$category->name, 'admin.products.categories.edit');

            return $category;
        });
    }

    public function delete(ProductCategory $category): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($category): void {
            $category = ProductCategory::query()->lockForUpdate()->findOrFail($category->id);
            foreach ($this->features->form('Products', 'category')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    $files->replace(null, $category->extra_fields[$key] ?? null, 'products/categories/'.$category->id.'/features', true);
                }
            }
            $this->media->detachUsages($category);
            $category->delete();
        });
    }
}
