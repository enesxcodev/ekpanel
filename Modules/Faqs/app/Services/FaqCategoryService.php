<?php

namespace Modules\Faqs\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
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
            $category->name = $data['name'];
            $category->save();
            $category->extra_fields = $this->values->prepare(
                $this->features->form('Faqs', 'category')['fields'],
                $data,
                $category->extra_fields ?? [],
                'faqs/categories/'.$category->id.'/features',
                $files,
                $actor,
            );
            $category->save();

            $this->values->syncMediaUsages($this->features->form('Faqs', 'category')['fields'], $category, $this->media, 'Soru kategorisi / '.$category->name, 'admin.faqs.categories.edit');

            return $category;
        });
    }

    public function delete(FaqCategory $category): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($category): void {
            $category = FaqCategory::query()->lockForUpdate()->findOrFail($category->id);
            foreach ($this->features->form('Faqs', 'category')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    $files->replace(null, $category->extra_fields[$key] ?? null, 'faqs/categories/'.$category->id.'/features', true);
                }
            }
            $this->media->detachUsages($category);
            $category->delete();
        });
    }
}
