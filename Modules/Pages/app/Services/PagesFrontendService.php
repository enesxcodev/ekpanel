<?php

namespace Modules\Pages\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Pages\Models\Page;
use Modules\Pages\Models\PageCategory;
use Nwidart\Modules\Facades\Module;

class PagesFrontendService
{
    /** @return Collection<int, Page> */
    public function getAll(?int $categoryId = null): Collection
    {
        return $this->visibleQuery($categoryId)?->get() ?? new Collection;
    }

    public function getFirst(?int $categoryId = null): ?Page
    {
        return $this->visibleQuery($categoryId)?->first();
    }

    public function find(int $id): ?Page
    {
        return $this->visibleQuery()?->whereKey($id)->first();
    }

    /** @return Builder<Page>|null */
    private function visibleQuery(?int $categoryId = null): ?Builder
    {
        if (! Module::find('Pages')?->isEnabled() || ! Schema::hasTable('pages') || ! Schema::hasTable('page_categories')) {
            return null;
        }

        $query = Page::query()->with(['category', 'imageMedia'])->where('status', 'published')->orderBy('sort_order')->orderBy('title')->orderBy('id');

        if ($categoryId !== null) {
            $category = PageCategory::query()->find($categoryId);
            $query->whereIn('page_category_id', $category === null ? [] : [$category->id, ...$category->descendantIds()]);
        }

        return $query;
    }
}
