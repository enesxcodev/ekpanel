<?php

namespace Modules\Blog\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogContent;
use Nwidart\Modules\Facades\Module;

class BlogFrontendService
{
    /** @return Collection<int, BlogContent> */
    public function getAll(?int $categoryId = null): Collection
    {
        return $this->visibleQuery($categoryId)?->get() ?? new Collection;
    }

    public function getFirst(?int $categoryId = null): ?BlogContent
    {
        return $this->visibleQuery($categoryId)?->first();
    }

    public function find(int $id): ?BlogContent
    {
        return $this->visibleQuery()?->whereKey($id)->first();
    }

    /** @return Builder<BlogContent>|null */
    private function visibleQuery(?int $categoryId = null): ?Builder
    {
        if (! Module::find('Blog')?->isEnabled() || ! Schema::hasTable('blog_contents') || ! Schema::hasTable('blog_categories')) {
            return null;
        }

        $query = BlogContent::query()->with(['category', 'imageMedia'])->where('status', 'published');

        if ($categoryId !== null) {
            $category = BlogCategory::query()->find($categoryId);
            $query->whereIn('blog_category_id', $category === null ? [] : [$category->id, ...$category->descendantIds()]);
        }

        return $query;
    }
}
