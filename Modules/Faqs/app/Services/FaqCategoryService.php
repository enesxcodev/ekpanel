<?php

namespace Modules\Faqs\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use Modules\Faqs\Models\FaqCategory;

class FaqCategoryService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private MediaService $media) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?FaqCategory $category = null, ?Admin $actor = null): FaqCategory
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $category, $actor): FaqCategory {
            $category = $category === null ? new FaqCategory : FaqCategory::query()->lockForUpdate()->findOrFail($category->id);
            $category->save();
            $language = isset($data['lang_id']) ? Language::query()->where('is_active', true)->findOrFail($data['lang_id']) : app(FaqLanguageService::class)->forFrontend();
            $translation = $category->translations()->firstOrNew(['lang_id' => $language->id]);
            $translation->name = $data['name'];
            $translation->extra_fields = $this->values->prepare($this->features->form('Faqs', 'category')['fields'], $data, $translation->extra_fields ?? [], 'faqs/categories/'.$category->id.'/translations/'.$language->id.'/features', $files, $actor);
            $translation->save();
            $this->values->syncMediaUsages($this->features->form('Faqs', 'category')['fields'], $translation, $this->media, 'Soru kategorisi / '.$translation->name, 'admin.faqs.categories.translations.edit');

            return $category;
        });
    }

    public function delete(FaqCategory $category): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($category): void {
            $category = FaqCategory::query()->lockForUpdate()->findOrFail($category->id);
            foreach ($category->translations()->get() as $translation) {
                foreach ($this->features->form('Faqs', 'category')['fields'] as $key => $definition) {
                    if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                        $files->replace(null, $translation->extra_fields[$key] ?? null, 'faqs/categories/'.$category->id.'/translations/'.$translation->lang_id.'/features', true);
                    }
                }
                $this->media->detachUsages($translation);
            }
            $this->media->detachUsages($category);
            $category->delete();
        });
    }
}
