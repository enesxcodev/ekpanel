<?php

namespace Modules\Pages\Services;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use Modules\Pages\Models\PageCategory;

class PageCategoryService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?PageCategory $category = null): PageCategory
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $category): PageCategory {
            $category = $category === null
                ? new PageCategory
                : PageCategory::query()->lockForUpdate()->findOrFail($category->id);
            $category->fill([
                'parent_id' => $data['parent_id'] ?? null,
                'name' => $data['name'],
                'short_description' => $data['short_description'] ?? null,
                'slug' => $data['slug'],
                'sort_order' => $data['sort_order'],
            ])->save();
            $category->extra_fields = $this->values->prepare(
                $this->features->form('Pages', 'category')['fields'],
                $data,
                $category->extra_fields ?? [],
                'pages/categories/'.$category->id.'/features',
                $files,
            );
            $category->save();

            return $category;
        });
    }

    public function delete(PageCategory $category): bool
    {
        return $this->files->transaction(function (FieldFiles $files) use ($category): bool {
            $category = PageCategory::query()->lockForUpdate()->findOrFail($category->id);
            if ($category->children()->exists() || $category->pages()->exists()) {
                return false;
            }

            foreach ($this->features->form('Pages', 'category')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile()) {
                    $files->replace(null, $category->extra_fields[$key] ?? null, 'pages/categories/'.$category->id.'/features', true);
                }
            }
            $category->delete();

            return true;
        });
    }
}
