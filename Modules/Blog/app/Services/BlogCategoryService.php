<?php

namespace Modules\Blog\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Settings\Language;
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
            $category->fill(['parent_id' => $data['parent_id'] ?? null, 'sort_order' => $data['sort_order']])->save();
            $language = isset($data['lang_id'])
                ? Language::query()->where('is_active', true)->findOrFail($data['lang_id'])
                : app(BlogLanguageService::class)->forPublic();
            $translation = $category->translations()->firstOrNew(['lang_id' => $language->id]);
            $translation->fill([
                'name' => $data['name'],
                'slug' => $data['slug'],
            ]);
            $translation->extra_fields = $this->values->prepare($this->features->form('Blog', 'category')['fields'], $data, $translation->extra_fields ?? [], 'blog/categories/'.$category->id.'/translations/'.$language->id.'/features', $files, $actor);
            $translation->save();
            $this->values->syncMediaUsages($this->features->form('Blog', 'category')['fields'], $translation, $this->media, 'İçerik kategorisi / '.$translation->name, 'admin.blog.categories.translations.edit');

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
            foreach ($category->translations()->get() as $translation) {
                foreach ($this->features->form('Blog', 'category')['fields'] as $key => $definition) {
                    if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                        $files->replace(null, $translation->extra_fields[$key] ?? null, 'blog/categories/'.$category->id.'/translations/'.$translation->lang_id.'/features', true);
                    }
                }
                $this->media->detachUsages($translation);
            }
            $this->media->detachUsages($category);
            $category->delete();

            return true;
        });
    }
}
