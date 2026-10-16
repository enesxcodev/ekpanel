<?php

namespace Modules\Pages\Services;

use Illuminate\Support\Facades\Route;
use Modules\Menus\Interfaces\MenuSource;
use Modules\Pages\Models\Page;
use Modules\Pages\Models\PageCategory;

class PageMenuSource implements MenuSource
{
    public function label(): string
    {
        return 'Sayfalar';
    }

    /** @return list<array{value: string, label: string}> */
    public function options(): array
    {
        $categories = PageCategory::query()->get(['id', 'parent_id', 'slug'])->keyBy('id');

        foreach ($categories as $category) {
            $category->setRelation('parent', $categories->get($category->parent_id));
        }

        return Page::query()->where('status', 'published')->orderBy('sort_order')->orderBy('title')->orderBy('id')
            ->get(['id', 'title', 'page_category_id'])->map(function (Page $page) use ($categories): array {
                $page->setRelation('category', $categories->get($page->page_category_id));

                return [
                    'value' => (string) $page->id,
                    'label' => ($page->category === null ? 'Kategorisiz' : '/'.$page->category->url_path).' / '.$page->title,
                ];
            })->sortBy('label')->values()->all();
    }

    /** @return array{label: string, url: ?string, error: ?string}|null */
    public function find(string $id): ?array
    {
        $page = Page::query()->with('category.parent.parent')->find($id);

        if ($page === null) {
            return null;
        }

        if ($page->status !== 'published') {
            return ['label' => $page->title, 'url' => null, 'error' => 'Sayfa yayında değil.'];
        }

        if (! Route::has('pages.show')) {
            return ['label' => $page->title, 'url' => null, 'error' => 'Sayfaların genel bağlantısı kullanılamıyor.'];
        }

        return ['label' => $page->title, 'url' => route('pages.show', ['path' => $page->url_path]), 'error' => null];
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
}
