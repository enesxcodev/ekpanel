<?php

namespace Modules\Services\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Settings\Language;
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
            $category->fill(['sort_order' => $data['sort_order'] ?? null, 'is_active' => $data['is_active'] ?? $category->is_active ?? true]);
            $category->save();

            $language = Language::query()->where('is_active', true)->findOrFail($data['lang_id']);
            $translation = $category->translations()->firstOrNew(['lang_id' => $language->id]);
            $translation->name = $data['name'];
            $translation->extra_fields = $this->values->prepare(
                $this->features->form('Services', 'category')['fields'],
                $data,
                $translation->extra_fields ?? [],
                'services/categories/'.$category->id.'/features',
                $files,
                $actor,
            );
            $translation->save();

            $this->values->syncMediaUsages($this->features->form('Services', 'category')['fields'], $translation, $this->media, 'Hizmet kategorisi / '.$translation->name, 'admin.services.categories.translations.edit');

            return $category;
        });
    }

    public function delete(ServiceCategory $category): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($category): void {
            $category = ServiceCategory::query()->lockForUpdate()->findOrFail($category->id);
            foreach ($category->translations as $translation) {
                foreach ($this->features->form('Services', 'category')['fields'] as $key => $definition) {
                    if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                        $files->replace(null, $translation->extra_fields[$key] ?? null, 'services/categories/'.$category->id.'/features', true);
                    }
                }
                $this->media->detachUsages($translation);
            }
            $this->media->detachUsages($category);
            $category->delete();
        });
    }
}
