<?php

namespace Modules\Documents\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Documents\Models\Document;
use Nwidart\Modules\Facades\Module;

class DocumentsFrontendService
{
    /** @return Collection<int, Document> */
    public function getAll(?int $categoryId = null): Collection
    {
        return $this->visibleQuery($categoryId)?->get() ?? new Collection;
    }

    public function getFirst(?int $categoryId = null): ?Document
    {
        return $this->visibleQuery($categoryId)?->first();
    }

    public function find(int $id): ?Document
    {
        return $this->visibleQuery()?->whereKey($id)->first();
    }

    /** @return Builder<Document>|null */
    private function visibleQuery(?int $categoryId = null): ?Builder
    {
        if (! Module::find('Documents')?->isEnabled() || ! Schema::hasTable('documents') || ! Schema::hasTable('document_categories')) {
            return null;
        }

        $query = Document::query()->with(['category', 'fileMedia'])->where('status', 'published');

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        return $query;
    }
}
