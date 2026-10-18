<?php

namespace Modules\Documents\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\HtmlSanitizer;
use App\Services\Media\ContentMediaService;
use App\Services\Media\MediaService;
use Modules\Documents\Models\Document;

class DocumentService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private HtmlSanitizer $sanitizer, private MediaService $media, private ContentMediaService $contentMedia) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Document $document = null, ?Admin $actor = null): Document
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $document, $actor): Document {
            $document = $document === null ? new Document : Document::query()->lockForUpdate()->findOrFail($document->id);
            $document->fill([
                'category_id' => $data['category_id'] ?? null,
                'name' => $data['name'],
                'content' => isset($data['content']) ? $this->sanitizer->sanitize($data['content']) : null,
                'slug' => $data['slug'],
                ...$this->contentMedia->prepare($document, $data, $this->contentMedia->definition('file', 'Dosya', 15360, ['png', 'jpg', 'jpeg', 'webp', 'pdf', 'doc', 'pptx']), $actor),
                'status' => $data['status'],
            ])->save();
            $document->extra_fields = $this->values->prepare(
                $this->features->form('Documents', 'document')['fields'],
                $data,
                $document->extra_fields ?? [],
                'documents/'.$document->id.'/features',
                $files,
                $actor,
            );
            $document->save();

            $this->contentMedia->syncUsage($document, 'file', 'Belge / '.$document->name, 'admin.documents.edit');
            $this->values->syncMediaUsages($this->features->form('Documents', 'document')['fields'], $document, $this->media, 'Belge / '.$document->name, 'admin.documents.edit');

            return $document;
        });
    }

    public function delete(Document $document): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($document): void {
            $document = Document::query()->lockForUpdate()->findOrFail($document->id);
            foreach ($this->features->form('Documents', 'document')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    $files->replace(null, $document->extra_fields[$key] ?? null, 'documents/'.$document->id.'/features', true);
                }
            }
            $this->media->detachUsages($document);
            $document->delete();
        });
    }
}
