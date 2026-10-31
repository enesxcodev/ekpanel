<?php

namespace Modules\Pages\Services;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Pages\Models\Page;
use Modules\Pages\Models\PageCategory;
use Nwidart\Modules\Facades\Module;

class PagesFrontendService
{
    /** @return Collection<int, Page> */
    public function getAll(?int $categoryId = null, ?string $languageCode = null): Collection
    {
        $pages = $this->visibleQuery($categoryId, $languageCode)?->get() ?? new Collection;

        return $pages->map(fn (Page $page): Page => $this->applyTranslation($page))->sortBy([['sort_order', 'asc'], ['title', 'asc'], ['id', 'asc']])->values();
    }

    public function getFirst(?int $categoryId = null, ?string $languageCode = null): ?Page
    {
        $page = $this->visibleQuery($categoryId, $languageCode)?->first();

        return $page ? $this->applyTranslation($page) : null;
    }

    public function find(int $id, ?string $languageCode = null): ?Page
    {
        $page = $this->visibleQuery(null, $languageCode)?->whereKey($id)->first();

        return $page ? $this->applyTranslation($page) : null;
    }

    /** @return Builder<Page>|null */
    private function visibleQuery(?int $categoryId = null, ?string $languageCode = null): ?Builder
    {
        if (! Module::find('Pages')?->isEnabled() || ! Schema::hasTable('pages') || ! Schema::hasTable('page_categories')) {
            return null;
        }

        $languageCode ??= request()->attributes->get('pages_language', PagesLanguageService::PrimaryCode);
        $language = Language::query()->where('code', $languageCode)->where('is_active', true)->first();
        if ($language === null) {
            return null;
        }
        request()->attributes->set('pages_language', $language->code);

        $query = Page::query()->with(['category.translations' => fn ($query) => $query->where('lang_id', $language->id), 'imageMedia', 'translations' => fn ($query) => $query->where('lang_id', $language->id)])
            ->where('status', 'published')->orderBy('sort_order')->orderBy('id');
        if (Schema::hasTable('page_translations')) {
            $query->whereHas('translations', fn ($translations) => $translations->where('lang_id', $language->id));
        }

        if ($categoryId !== null) {
            $category = PageCategory::query()->find($categoryId);
            $query->whereIn('page_category_id', $category === null ? [] : [$category->id, ...$category->descendantIds()]);
        }

        return $query;
    }

    private function applyTranslation(Page $page): Page
    {
        $translation = $page->translations->first();
        if ($translation === null) {
            return $page;
        }
        foreach (['title', 'slug', 'short_description', 'content', 'meta_title', 'meta_keywords', 'meta_description', 'extra_fields'] as $field) {
            $page->setAttribute($field, $translation->{$field});
        }

        if ($page->category !== null) {
            $categoryTranslation = $page->category->translations->first();
            if ($categoryTranslation !== null) {
                $page->category->setAttribute('name', $categoryTranslation->name);
                $page->category->setAttribute('slug', $categoryTranslation->slug);
            }
        }

        return $page;
    }
}
