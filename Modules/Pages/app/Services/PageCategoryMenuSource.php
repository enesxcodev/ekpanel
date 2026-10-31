<?php

namespace Modules\Pages\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Modules\Menus\Interfaces\LocalizedMenuSource;
use Modules\Menus\Interfaces\MenuSource;
use Modules\Pages\Models\Page;
use Modules\Pages\Models\PageCategory;

class PageCategoryMenuSource implements LocalizedMenuSource, MenuSource
{
    public function label(): string
    {
        return 'Sayfa kategorileri';
    }

    /** @return list<array{value: string, label: string}> */
    public function options(): array
    {
        return $this->optionsForLanguage($this->currentLanguageCode());
    }

    public function optionsForLanguage(string $languageCode): array
    {
        $language = app(PagesLanguageService::class)->forFrontend($languageCode);
        request()->attributes->set('pages_language', $language?->code ?? PagesLanguageService::PrimaryCode);
        $categories = PageCategory::query()->with(['translations' => fn ($query) => $query->where('lang_id', $language?->id)])
            ->orderBy('sort_order')->orderBy('id')->get(['id', 'parent_id', 'name', 'slug'])->keyBy('id');

        foreach ($categories as $category) {
            $category->setRelation('parent', $categories->get($category->parent_id));
            if ($translation = $category->translations->first()) {
                $category->setAttribute('name', $translation->name);
                $category->setAttribute('slug', $translation->slug);
            }
        }

        return $categories->map(fn (PageCategory $category): ?array => Schema::hasTable('page_category_translations') && ! $this->hasTranslatedCategoryChain($category) ? null : [
            'value' => (string) $category->id,
            'label' => '/'.$category->url_path.' — '.$category->name,
        ])->filter()->sortBy('label')->values()->all();
    }

    /** @return array{label: string, url: ?string, error: ?string}|null */
    public function find(string $id): ?array
    {
        return $this->findForLanguage($id, $this->currentLanguageCode());
    }

    public function findForLanguage(string $id, string $languageCode): ?array
    {
        $language = app(PagesLanguageService::class)->forFrontend($languageCode);
        $category = $this->categoriesForLanguage($language?->id)->get((int) $id);

        if ($category === null) {
            return null;
        }
        $translation = $category->translations->first();
        if ($translation === null && Schema::hasTable('page_category_translations')) {
            return ['label' => $category->name, 'url' => null, 'error' => 'Kategori seçilen dilde çevrilmemiş.'];
        }
        if (Schema::hasTable('page_category_translations') && ! $this->hasTranslatedCategoryChain($category)) {
            return ['label' => $translation?->name ?? $category->name, 'url' => null, 'error' => 'Kategori yolu seçilen dilde çevrilmemiş.'];
        }

        return ['label' => $translation?->name ?? $category->name, 'url' => null, 'error' => null];
    }

    public function supportsChildren(): bool
    {
        return true;
    }

    /** @return list<array{label: string, url: string, new_tab: bool, children: array}> */
    public function children(string $id): array
    {
        return $this->childrenForLanguage($id, $this->currentLanguageCode());
    }

    public function childrenForLanguage(string $id, string $languageCode): array
    {
        $language = app(PagesLanguageService::class)->forFrontend($languageCode);
        request()->attributes->set('pages_language', $language?->code ?? PagesLanguageService::PrimaryCode);
        $category = $this->categoriesForLanguage($language?->id)->get((int) $id);

        if ($category === null || ! Route::has('pages.show')) {
            return [];
        }

        $categoryMap = $this->categoriesForLanguage($language?->id);

        return $category->pages()->where('status', 'published')->with(['translations' => fn ($query) => $query->where('lang_id', $language?->id)])->orderBy('id')->get()
            ->map(function (Page $page) use ($language, $categoryMap): ?array {
                $translation = $page->translations->first();
                if ($translation === null && Schema::hasTable('page_translations')) {
                    return null;
                }
                $pageCategory = $categoryMap->get($page->page_category_id);
                if (Schema::hasTable('page_category_translations') && ! $this->hasTranslatedCategoryChain($pageCategory)) {
                    return null;
                }
                if ($translation !== null) {
                    $page->setAttribute('title', $translation->title);
                    $page->setAttribute('slug', $translation->slug);
                }

                return [
                    'label' => $translation?->title ?? $page->title,
                    'url' => $language && $language->code !== PagesLanguageService::PrimaryCode
                        ? route('pages.localized.show', ['lang' => $language->code, 'path' => $page->url_path])
                        : route('pages.show', ['path' => $page->url_path]),
                    'new_tab' => false,
                    'children' => [],
                ];
            })->filter()->all();
    }

    private function categoriesForLanguage(?int $languageId): Collection
    {
        $categories = PageCategory::query()->with(['translations' => fn ($query) => $query->where('lang_id', $languageId)])
            ->get(['id', 'parent_id', 'name', 'slug'])->keyBy('id');
        foreach ($categories as $category) {
            $category->setRelation('parent', $categories->get($category->parent_id));
            if ($translation = $category->translations->first()) {
                $category->setAttribute('name', $translation->name);
                $category->setAttribute('slug', $translation->slug);
            }
        }

        return $categories;
    }

    private function currentLanguageCode(): string
    {
        return (string) (request()->route('lang') ?? app(PagesLanguageService::class)->forFrontend()?->code ?? PagesLanguageService::PrimaryCode);
    }

    private function hasTranslatedCategoryChain(?PageCategory $category): bool
    {
        $seen = [];
        while ($category !== null) {
            if (isset($seen[$category->id]) || $category->translations->isEmpty()) {
                return false;
            }
            $seen[$category->id] = true;
            $category = $category->parent;
        }

        return true;
    }
}
