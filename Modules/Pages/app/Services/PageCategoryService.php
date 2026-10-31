<?php

namespace Modules\Pages\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use Modules\Pages\Models\PageCategory;
use Modules\Pages\Models\PageCategoryTranslation;

class PageCategoryService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private MediaService $media) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?PageCategory $category = null, ?Admin $actor = null): PageCategory
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $category, $actor): PageCategory {
            $language = Language::query()->whereKey($data['lang_id'] ?? null)->where('is_active', true)->firstOrFail();
            $category = $category === null
                ? new PageCategory
                : PageCategory::query()->lockForUpdate()->findOrFail($category->id);
            $translation = $category->translations()->where('lang_id', $language->id)->first() ?? new PageCategoryTranslation(['lang_id' => $language->id]);
            $isNew = ! $category->exists;
            $categoryData = [
                'parent_id' => $data['parent_id'] ?? null,
                'sort_order' => $data['sort_order'],
            ];
            $legacyPrimary = $language->code === PagesLanguageService::PrimaryCode;
            if ($legacyPrimary) {
                $categoryData += ['name' => $data['name'], 'short_description' => $data['short_description'] ?? null, 'slug' => $data['slug']];
            } elseif ($isNew) {
                $categoryData += ['name' => 'Category', 'short_description' => null, 'slug' => 'category-pending'];
            }
            $category->fill($categoryData)->save();
            if ($isNew && ! $legacyPrimary) {
                $category->forceFill(['name' => 'Category '.$category->id, 'slug' => 'category-'.$category->id])->save();
            }
            $translation->fill([
                'page_category_id' => $category->id,
                'lang_id' => $language->id,
                'scope_id' => $category->parent_id ?? 0,
                'name' => $data['name'],
                'slug' => $data['slug'],
                'short_description' => $data['short_description'] ?? null,
            ])->save();
            $translation->extra_fields = $this->values->prepare(
                $this->features->form('Pages', 'category')['fields'],
                $data,
                $translation->extra_fields ?? [],
                'pages/categories/'.$category->id.'/translations/'.$language->id.'/features',
                $files,
                $actor,
            );
            $translation->save();
            if ($legacyPrimary) {
                $category->extra_fields = $translation->extra_fields;
                $category->save();
            }

            $this->values->syncMediaUsages($this->features->form('Pages', 'category')['fields'], $translation, $this->media, 'Sayfa kategorisi / '.$translation->name, 'admin.pages.categories.edit');

            return $category;
        });
    }

    public function delete(PageCategory $category): bool
    {
        return $this->files->transaction(function (FieldFiles $files) use ($category): bool {
            $category = PageCategory::query()->with('translations')->lockForUpdate()->findOrFail($category->id);
            if ($category->children()->exists() || $category->pages()->exists()) {
                return false;
            }

            foreach ($this->features->form('Pages', 'category')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    foreach ($category->translations as $translation) {
                        $files->replace(null, $translation->extra_fields[$key] ?? null, 'pages/categories/'.$category->id.'/translations/'.$translation->lang_id.'/features', true);
                    }
                }
            }
            $this->media->detachUsages($category);
            foreach ($category->translations as $translation) {
                $this->media->detachUsages($translation);
            }
            $category->delete();

            return true;
        });
    }
}
