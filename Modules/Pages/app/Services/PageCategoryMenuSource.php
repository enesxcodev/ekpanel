<?php

namespace Modules\Pages\Services;

use Illuminate\Support\Facades\Route;
use Modules\Menus\Interfaces\MenuSource;
use Modules\Pages\Models\Page;
use Modules\Pages\Models\PageCategory;

class PageCategoryMenuSource implements MenuSource
{
    public function label(): string
    {
        return 'Sayfa kategorileri';
    }

    /** @return list<array{value: string, label: string}> */
    public function options(): array
    {
        $categories = PageCategory::query()->orderBy('sort_order')->orderBy('name')->orderBy('id')
            ->get(['id', 'parent_id', 'name', 'slug'])->keyBy('id');

        foreach ($categories as $category) {
            $category->setRelation('parent', $categories->get($category->parent_id));
        }

        return $categories->map(fn (PageCategory $category): array => [
            'value' => (string) $category->id,
            'label' => '/'.$category->url_path.' — '.$category->name,
        ])->sortBy('label')->values()->all();
    }

    /** @return array{label: string, url: ?string, error: ?string}|null */
    public function find(string $id): ?array
    {
        $category = PageCategory::query()->find($id);

        return $category === null ? null : ['label' => $category->name, 'url' => null, 'error' => null];
    }

    public function supportsChildren(): bool
    {
        return true;
    }

    /** @return list<array{label: string, url: string, new_tab: bool, children: array}> */
    public function children(string $id): array
    {
        $category = PageCategory::query()->find($id);

        if ($category === null || ! Route::has('pages.show')) {
            return [];
        }

        return $category->pages()->where('status', 'published')->with('category.parent.parent')->orderBy('id')->get()
            ->map(fn (Page $page): array => [
                'label' => $page->title,
                'url' => route('pages.show', ['path' => $page->url_path]),
                'new_tab' => false,
                'children' => [],
            ])->all();
    }
}
