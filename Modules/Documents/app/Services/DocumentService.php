<?php

namespace Modules\Documents\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\HtmlSanitizer;
use App\Services\Media\ContentMediaService;
use App\Services\Media\MediaService;
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentTranslation;

class DocumentService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private HtmlSanitizer $sanitizer, private MediaService $media, private ContentMediaService $contentMedia) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Document $document = null, ?Admin $actor = null): Document
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $document, $actor): Document {
            $document = $document === null ? new Document : Document::query()->lockForUpdate()->findOrFail($document->id);
            $language = Language::query()->whereKey($data['lang_id'] ?? null)->where('is_active', true)->firstOrFail();
            $document->fill([
                'category_id' => $data['category_id'] ?? null,
                ...$this->contentMedia->prepare($document, $data, $this->contentMedia->definition('file', 'Dosya', 15360, ['png', 'jpg', 'jpeg', 'webp', 'pdf', 'doc', 'pptx']), $actor),
                'status' => $data['status'],
            ])->save();
            $translation = $document->translations()->where('lang_id', $language->id)->first() ?? new DocumentTranslation(['lang_id' => $language->id]);
            $translation->fill([
                'document_id' => $document->id,
                'lang_id' => $language->id,
                'name' => $data['name'],
                'content' => isset($data['content']) ? $this->sanitizer->sanitize($data['content']) : null,
                'slug' => $data['slug'],
            ]);
            $translation->extra_fields = $this->values->prepare(
                $this->features->form('Documents', 'document')['fields'],
                $data,
                $translation->extra_fields ?? [],
                'documents/'.$document->id.'/translations/'.$language->id.'/features',
                $files,
                $actor,
            );
            $translation->save();

            $this->contentMedia->syncUsage($document, 'file', 'Belge / '.$translation->name, 'admin.documents.edit');
            $this->values->syncMediaUsages($this->features->form('Documents', 'document')['fields'], $translation, $this->media, 'Belge / '.$translation->name, 'admin.documents.translations.edit');

            return $document->load(['translations.language']);
        });
    }

    public function delete(Document $document): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($document): void {
            $document = Document::query()->with('translations')->lockForUpdate()->findOrFail($document->id);
            foreach ($document->translations as $translation) {
                foreach ($this->features->form('Documents', 'document')['fields'] as $key => $definition) {
                    if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                        $files->replace(null, $translation->extra_fields[$key] ?? null, 'documents/'.$document->id.'/translations/'.$translation->lang_id.'/features', true);
                    }
                }
                $this->media->detachUsages($translation);
            }
            $this->media->detachUsages($document);
            $document->delete();
        });
    }
}
