<?php

namespace Modules\Documents\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentCategory;
use Nwidart\Modules\Facades\Module;

class DocumentsFrontendService
{
    public function __construct(private DocumentLanguageService $languages) {}

    /** @return Collection<int, Document> */
    public function getAll(?int $categoryId = null, ?string $languageCode = null): Collection
    {
        return $this->visibleQuery($categoryId, $languageCode)?->get()->each(fn (Document $document) => $this->localize($document)) ?? new Collection;
    }

    public function getFirst(?int $categoryId = null, ?string $languageCode = null): ?Document
    {
        $document = $this->visibleQuery($categoryId, $languageCode)?->first();

        return $document ? $this->localize($document) : null;
    }

    public function find(int $id, ?string $languageCode = null): ?Document
    {
        $document = $this->visibleQuery(null, $languageCode)?->whereKey($id)->first();

        return $document ? $this->localize($document) : null;
    }

    /** @return Builder<Document>|null */
    private function visibleQuery(?int $categoryId = null, ?string $languageCode = null): ?Builder
    {
        if (! Module::find('Documents')?->isEnabled() || ! Schema::hasTable('languages') || ! Schema::hasTable('documents') || ! Schema::hasTable('document_categories') || ! Schema::hasTable('document_translations') || ! Schema::hasTable('document_category_translations')) {
            return null;
        }
        $language = $this->languages->forFrontend($languageCode);
        if ($language === null) {
            return null;
        }

        $query = Document::query()
            ->where('status', 'published')
            ->whereHas('translations', fn ($translation) => $translation->where('lang_id', $language->id))
            ->with([
                'translations' => fn ($translation) => $translation->where('lang_id', $language->id),
                'category.translations' => fn ($translation) => $translation->where('lang_id', $language->id),
                'fileMedia',
            ]);

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        return $query;
    }

    private function localize(Document $document): Document
    {
        $translation = $document->translations->first();
        $document->setRelation('selectedTranslation', $translation);
        if ($translation !== null) {
            foreach (['name', 'content', 'slug', 'extra_fields'] as $field) {
                $document->setAttribute($field, $translation->{$field});
            }
        }
        $category = $document->category;
        if ($category instanceof DocumentCategory) {
            $categoryTranslation = $category->translations->first();
            $category->setRelation('selectedTranslation', $categoryTranslation);
            if ($categoryTranslation !== null) {
                $category->setAttribute('name', $categoryTranslation->name);
                $category->setAttribute('extra_fields', $categoryTranslation->extra_fields);
            }
            if ($category->getRelation('selectedTranslation') === null) {
                $document->setRelation('category', null);
            }
        }

        return $document;
    }
}
