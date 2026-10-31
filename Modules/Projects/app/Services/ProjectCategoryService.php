<?php

namespace Modules\Projects\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Modules\Projects\Models\ProjectCategory;

class ProjectCategoryService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private MediaService $media) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?ProjectCategory $category, Admin $actor): ProjectCategory
    {
        return Cache::lock('projects-category-tree', 30)->block(5, function () use ($data, $category, $actor): ProjectCategory {
            return $this->files->transaction(function (FieldFiles $files) use ($data, $category, $actor): ProjectCategory {
                $category = $category ? ProjectCategory::query()->lockForUpdate()->findOrFail($category->id) : new ProjectCategory;

                $language = Language::query()->where('is_active', true)->findOrFail($data['lang_id']);
                $isPrimary = $language->code === ProjectsLanguageService::PrimaryCode;
                if (! $category->exists && ! $isPrimary) {
                    throw ValidationException::withMessages(['lang_id' => 'Yeni proje kategorileri Türkçe oluşturulmalıdır.']);
                }
                if (! $category->exists || $isPrimary) {
                    $category->fill(['icon' => $data['icon'] ?? null])->save();
                }
                $definitions = $this->features->form('Projects', 'category')['fields'];
                $translation = $category->translations()->firstOrNew(['project_category_id' => $category->id, 'lang_id' => $language->id]);
                $translation->fill(['name' => $data['name'], 'slug' => $data['slug']]);
                $translation->extra_fields = $this->values->prepare($definitions, $data, $translation->extra_fields ?? [], 'projects/categories/'.$category->id.'/translations/'.$language->id.'/features', $files, $actor);
                $translation->save();
                $this->values->syncMediaUsages($definitions, $translation, $this->media, 'Kategori / '.$translation->name, 'admin.projects.categories.translations.edit');

                return $category;
            });
        });
    }

    public function delete(ProjectCategory $category): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($category): void {
            $category = ProjectCategory::query()->lockForUpdate()->findOrFail($category->id);
            if ($category->projects()->exists()) {
                throw ValidationException::withMessages(['category' => 'İçerik bulunan kategori silinemez.']);
            }
            foreach ($category->translations as $translation) {
                foreach ($this->features->form('Projects', 'category')['fields'] as $key => $definition) {
                    if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                        $files->replace(null, $translation->extra_fields[$key] ?? null, 'projects/categories/'.$category->id.'/translations/'.$translation->lang_id.'/features', true);
                    }
                }
                $this->media->detachUsages($translation);
            }
            $this->media->detachUsages($category);
            $category->delete();
        });
    }
}
