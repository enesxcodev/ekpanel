<?php

namespace Modules\Blog\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Schema;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogContent;
use Nwidart\Modules\Facades\Module;

class BlogFrontendService
{
    public function __construct(private BlogLanguageService $languages) {}

    /** @return Collection<int, BlogContent> */
    public function getAll(?int $categoryId = null, ?string $languageCode = null): Collection
    {
        return $this->visibleQuery($categoryId, $languageCode)?->get()->each(fn (BlogContent $content) => $this->localize($content)) ?? new Collection;
    }

    public function getFirst(?int $categoryId = null, ?string $languageCode = null): ?BlogContent
    {
        $content = $this->visibleQuery($categoryId, $languageCode)?->first();

        return $content ? $this->localize($content) : null;
    }

    public function find(int $id, ?string $languageCode = null): ?BlogContent
    {
        $content = $this->visibleQuery(null, $languageCode)?->whereKey($id)->first();

        return $content ? $this->localize($content) : null;
    }

    public function paginate(int $perPage = 12, ?int $categoryId = null, ?string $languageCode = null): ?LengthAwarePaginator
    {
        $query = $this->visibleQuery($categoryId, $languageCode);
        if ($query === null) {
            return null;
        }
        $paginator = $query->latest('published_at')->orderByDesc('id')->paginate($perPage)->withQueryString();
        $paginator->getCollection()->each(fn (BlogContent $content) => $this->localize($content));

        return $paginator;
    }

    public function findBySlug(string $slug, ?string $languageCode = null): ?BlogContent
    {
        $code = $languageCode ?? request()->attributes->get('blog_language', BlogLanguageService::PrimaryCode);
        $content = $this->visibleQuery(null, $code)?->whereHas('translations', fn ($query) => $query->where('slug', $slug)->whereHas('language', fn ($language) => $language->where('code', $code)))->first();

        return $content ? $this->localize($content) : null;
    }

    private function localize(BlogContent $content): BlogContent
    {
        $translation = $content->getRelation('translations')->first();
        $content->setRelation('selectedTranslation', $translation);

        return $content;
    }

    /** @return Builder<BlogContent>|null */
    private function visibleQuery(?int $categoryId = null, ?string $languageCode = null): ?Builder
    {
        if (! Module::find('Blog')?->isEnabled() || ! Schema::hasTable('languages') || ! Schema::hasTable('blog_contents') || ! Schema::hasTable('blog_categories') || ! Schema::hasTable('blog_content_translations') || ! Schema::hasTable('blog_category_translations')) {
            return null;
        }
        $languageCode ??= request()->attributes->get('blog_language', BlogLanguageService::PrimaryCode);
        $language = $this->languages->forFrontend($languageCode);
        if ($language === null) {
            return null;
        }
        $categories = BlogCategory::query()->with(['translations' => fn ($query) => $query->where('lang_id', $language->id)])->get()->keyBy('id');
        $visibleIds = [];
        foreach ($categories as $category) {
            $cursor = $category;
            $visited = [];
            $valid = true;
            while ($cursor !== null) {
                if (isset($visited[$cursor->id]) || $cursor->translations->isEmpty()) {
                    $valid = false;
                    break;
                }
                $visited[$cursor->id] = true;
                $parentId = $cursor->parent_id;
                $cursor = $parentId === null ? null : $categories->get($parentId);
                if ($parentId !== null && $cursor === null) {
                    $valid = false;
                }
            }
            if ($valid) {
                $visibleIds[] = $category->id;
            }
        }
        foreach ($categories as $category) {
            $translation = $category->translations->first();
            $category->setRelation('selectedTranslation', $translation);
            $category->setRelation('parent', $categories->get($category->parent_id));
        }
        if ($categoryId !== null) {
            $descendants = [$categoryId];
            $pending = [$categoryId];
            while ($pending !== []) {
                $children = $categories->filter(fn (BlogCategory $category): bool => in_array($category->parent_id, $pending, true) && ! in_array($category->id, $descendants, true))->modelKeys();
                $descendants = [...$descendants, ...$children];
                $pending = $children;
            }
            $visibleIds = array_intersect($visibleIds, $descendants);
        }

        return BlogContent::query()->where('status', 'published')->whereIn('blog_category_id', $visibleIds)
            ->whereHas('translations', fn ($query) => $query->where('lang_id', $language->id))
            ->with(['translations' => fn ($query) => $query->where('lang_id', $language->id), 'imageMedia', 'author', 'tags'])
            ->afterQuery(function (Collection $contents) use ($categories): Collection {
                foreach ($contents as $content) {
                    $content->setRelation('category', $categories->get($content->blog_category_id));
                }

                return $contents;
            });
    }
}
