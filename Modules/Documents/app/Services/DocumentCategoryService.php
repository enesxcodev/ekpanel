<?php

namespace Modules\Documents\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use Modules\Documents\Models\DocumentCategory;
use Modules\Documents\Models\DocumentCategoryTranslation;

class DocumentCategoryService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private MediaService $media) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?DocumentCategory $category = null, ?Admin $actor = null): DocumentCategory
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $category, $actor): DocumentCategory {
            $category = $category === null ? new DocumentCategory : DocumentCategory::query()->lockForUpdate()->findOrFail($category->id);
            $language = Language::query()->whereKey($data['lang_id'] ?? null)->where('is_active', true)->firstOrFail();
            $category->icon = $data['icon'] ?? null;
            $category->save();
            $translation = $category->translations()->where('lang_id', $language->id)->first() ?? new DocumentCategoryTranslation(['lang_id' => $language->id]);
            $translation->fill(['document_category_id' => $category->id, 'lang_id' => $language->id, 'name' => $data['name']]);
            $translation->extra_fields = $this->values->prepare(
                $this->features->form('Documents', 'category')['fields'],
                $data,
                $translation->extra_fields ?? [],
                'documents/categories/'.$category->id.'/translations/'.$language->id.'/features',
                $files,
                $actor,
            );
            $translation->save();

            $this->values->syncMediaUsages($this->features->form('Documents', 'category')['fields'], $translation, $this->media, 'Belge kategorisi / '.$translation->name, 'admin.documents.categories.translations.edit');

            return $category->load(['translations.language']);
        });
    }

    public function delete(DocumentCategory $category): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($category): void {
            $category = DocumentCategory::query()->with('translations')->lockForUpdate()->findOrFail($category->id);
            foreach ($category->translations as $translation) {
                foreach ($this->features->form('Documents', 'category')['fields'] as $key => $definition) {
                    if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                        $files->replace(null, $translation->extra_fields[$key] ?? null, 'documents/categories/'.$category->id.'/translations/'.$translation->lang_id.'/features', true);
                    }
                }
                $this->media->detachUsages($translation);
            }
            $this->media->detachUsages($category);
            $category->delete();
        });
    }
}
