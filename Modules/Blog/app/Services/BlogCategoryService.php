<?php

namespace Modules\Blog\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use Modules\Blog\Models\BlogCategory;

class BlogCategoryService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private MediaService $media) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?BlogCategory $category = null, ?Admin $actor = null): BlogCategory
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $category, $actor): BlogCategory {
            $category = $category === null ? new BlogCategory : BlogCategory::query()->lockForUpdate()->findOrFail($category->id);
            $category->fill(['parent_id' => $data['parent_id'] ?? null, 'name' => $data['name'], 'slug' => $data['slug'], 'sort_order' => $data['sort_order']])->save();
            $category->extra_fields = $this->values->prepare($this->features->form('Blog', 'category')['fields'], $data, $category->extra_fields ?? [], 'blog/categories/'.$category->id.'/features', $files, $actor);
            $category->save();
            $this->values->syncMediaUsages($this->features->form('Blog', 'category')['fields'], $category, $this->media, 'İçerik kategorisi / '.$category->name, 'admin.blog.categories.edit');

            return $category;
        });
    }

    public function delete(BlogCategory $category): bool
    {
        return $this->files->transaction(function (FieldFiles $files) use ($category): bool {
            $category = BlogCategory::query()->lockForUpdate()->findOrFail($category->id);
            if ($category->children()->exists() || $category->contents()->exists()) {
                return false;
            }
            foreach ($this->features->form('Blog', 'category')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    $files->replace(null, $category->extra_fields[$key] ?? null, 'blog/categories/'.$category->id.'/features', true);
                }
            }
            $this->media->detachUsages($category);
            $category->delete();

            return true;
        });
    }
}
