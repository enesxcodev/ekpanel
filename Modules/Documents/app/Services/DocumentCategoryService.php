<?php

namespace Modules\Documents\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use Modules\Documents\Models\DocumentCategory;

class DocumentCategoryService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private MediaService $media) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?DocumentCategory $category = null, ?Admin $actor = null): DocumentCategory
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $category, $actor): DocumentCategory {
            $category = $category === null ? new DocumentCategory : DocumentCategory::query()->lockForUpdate()->findOrFail($category->id);
            $category->name = $data['name'];
            $category->icon = $data['icon'] ?? null;
            $category->save();
            $category->extra_fields = $this->values->prepare(
                $this->features->form('Documents', 'category')['fields'],
                $data,
                $category->extra_fields ?? [],
                'documents/categories/'.$category->id.'/features',
                $files,
                $actor,
            );
            $category->save();

            $this->values->syncMediaUsages($this->features->form('Documents', 'category')['fields'], $category, $this->media, 'Belge kategorisi / '.$category->name, 'admin.documents.categories.edit');

            return $category;
        });
    }

    public function delete(DocumentCategory $category): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($category): void {
            $category = DocumentCategory::query()->lockForUpdate()->findOrFail($category->id);
            foreach ($this->features->form('Documents', 'category')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    $files->replace(null, $category->extra_fields[$key] ?? null, 'documents/categories/'.$category->id.'/features', true);
                }
            }
            $this->media->detachUsages($category);
            $category->delete();
        });
    }
}
