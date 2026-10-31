<?php

namespace Modules\Pages\Services;

use Illuminate\Support\Facades\Route;
use Modules\Menus\Interfaces\LocalizedMenuSource;
use Modules\Menus\Interfaces\MenuSource;
use Modules\Pages\Models\Page;
use Modules\Pages\Models\PageCategory;

class PageMenuSource implements LocalizedMenuSource, MenuSource
{
    public function label(): string
    {
        return 'Sayfalar';
    }

    /** @return list<array{value: string, label: string}> */
    public function options(): array
    {
        return $this->optionsForLanguage($this->currentLanguageCode());
    }

    /** @return list<array{value: string, label: string}> */
    public function optionsForLanguage(string $languageCode): array
    {
        $language = app(PagesLanguageService::class)->forFrontend($languageCode);
        if ($language === null) {
            return [];
        }
        request()->attributes->set('pages_language', $language->code);
        $categories = PageCategory::query()->with(['translations' => fn ($query) => $query->where('lang_id', $language?->id)])
            ->get(['id', 'parent_id', 'slug'])->keyBy('id');

        foreach ($categories as $category) {
            $category->setRelation('parent', $categories->get($category->parent_id));
            $categoryTranslation = $category->translations->first();
            if ($categoryTranslation !== null) {
                $category->setAttribute('slug', $categoryTranslation->slug);
                $category->setAttribute('name', $categoryTranslation->name);
            }
        }

        $pages = Page::query()->with(['translations' => fn ($query) => $query->where('lang_id', $language?->id)])
            ->where('status', 'published')->orderBy('sort_order')->orderBy('id')
            ->get(['id', 'title', 'page_category_id']);

        return $pages->map(function (Page $page) use ($categories): ?array {
            $translation = $page->translations->first();
            if ($translation === null) {
                return null;
            }
            $page->setAttribute('title', $translation->title);
            $page->setAttribute('slug', $translation->slug);
            $page->setRelation('category', $categories->get($page->page_category_id));
            if (! $this->hasTranslatedCategoryChain($page->category)) {
                return null;
            }

            return [
                'value' => (string) $page->id,
                'label' => ($page->category === null ? 'Kategorisiz' : '/'.$page->category->url_path).' / '.$page->title,
            ];
        })->filter()->sortBy('label')->values()->all();
    }

    /** @return array{label: string, url: ?string, error: ?string}|null */
    public function find(string $id): ?array
    {
        return $this->findForLanguage($id, $this->currentLanguageCode());
    }

    /** @return array{label: string, url: ?string, error: ?string}|null */
    public function findForLanguage(string $id, string $languageCode): ?array
    {
        $language = app(PagesLanguageService::class)->forFrontend($languageCode);
        if ($language === null) {
            return ['label' => 'Sayfa', 'url' => null, 'error' => 'Seçilen dil etkin değil.'];
        }
        request()->attributes->set('pages_language', $language->code);
        $page = Page::query()->with(['category.parent.parent', 'category.translations' => fn ($query) => $query->where('lang_id', $language->id), 'category.parent.translations' => fn ($query) => $query->where('lang_id', $language->id), 'category.parent.parent.translations' => fn ($query) => $query->where('lang_id', $language->id), 'translations' => fn ($query) => $query->where('lang_id', $language->id)])->find($id);

        if ($page === null) {
            return null;
        }

        if ($page->status !== 'published') {
            return ['label' => $page->title, 'url' => null, 'error' => 'Sayfa yayında değil.'];
        }

        $translation = $page->translations->first();
        if ($translation === null) {
            return ['label' => $page->title, 'url' => null, 'error' => 'Sayfa seçilen dilde çevrilmemiş.'];
        }
        if (! $this->hasTranslatedCategoryChain($page->category)) {
            return ['label' => $translation->title, 'url' => null, 'error' => 'Sayfanın kategori yolu seçilen dilde çevrilmemiş.'];
        }
        $page->setAttribute('title', $translation->title);
        $page->setAttribute('slug', $translation->slug);

        if (! Route::has('pages.show')) {
            return ['label' => $page->title, 'url' => null, 'error' => 'Sayfaların genel bağlantısı kullanılamıyor.'];
        }

        $url = $language && $language->code !== PagesLanguageService::PrimaryCode
            ? route('pages.localized.show', ['lang' => $language->code, 'path' => $page->url_path])
            : route('pages.show', ['path' => $page->url_path]);

        return ['label' => $page->title, 'url' => $url, 'error' => null];
    }

    public function supportsChildren(): bool
    {
        return false;
    }

    /** @return list<array{label: string, url: string, new_tab: bool, children: array}> */
    public function children(string $id): array
    {
        return [];
    }

    /** @return list<array{label: string, url: string, new_tab: bool, children: array}> */
    public function childrenForLanguage(string $id, string $languageCode): array
    {
        return [];
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
