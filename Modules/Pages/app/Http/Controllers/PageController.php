<?php

namespace Modules\Pages\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Pages\DataTables\PagesDataTable;
use Modules\Pages\Http\Requests\SavePageRequest;
use Modules\Pages\Models\Page;
use Modules\Pages\Models\PageCategory;
use Modules\Pages\Services\PageService;

class PageController extends Controller
{
    public function index(): View
    {
        $this->authorize('pages.view');

        return view('pages::pages.index');
    }

    public function data(PagesDataTable $dataTable): JsonResponse
    {
        $this->authorize('pages.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('pages.create');
        $featureForm = $features->form('Pages', 'page');
        $featureFields = $values->fields($featureForm['fields'], []);
        $categoryTree = $this->categoryTree();

        return view('pages::pages.create', compact('featureForm', 'featureFields', 'categoryTree'));
    }

    public function store(SavePageRequest $request, PageService $service): RedirectResponse
    {
        $service->save($request->validated());

        return redirect()->route('admin.pages.index')->with('notification', ['type' => 'success', 'message' => 'Sayfa başarıyla eklendi.']);
    }

    public function edit(Page $page, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('pages.update');
        $featureForm = $features->form('Pages', 'page');
        $featureFields = $values->fields($featureForm['fields'], $page->extra_fields ?? []);
        $categoryTree = $this->categoryTree();

        return view('pages::pages.edit', compact('page', 'featureForm', 'featureFields', 'categoryTree'));
    }

    public function update(SavePageRequest $request, Page $page, PageService $service): RedirectResponse
    {
        $service->save($request->validated(), $page);

        return redirect()->route('admin.pages.index')->with('notification', ['type' => 'success', 'message' => 'Sayfa başarıyla güncellendi.']);
    }

    public function destroy(Page $page, PageService $service): RedirectResponse
    {
        $this->authorize('pages.delete');
        $service->delete($page);

        return redirect()->route('admin.pages.index')->with('notification', ['type' => 'success', 'message' => 'Sayfa başarıyla silindi.']);
    }

    public function show(string $path, PageService $service): View
    {
        $segments = array_values(array_filter(explode('/', trim($path, '/')), fn (string $segment): bool => $segment !== ''));
        abort_if($segments === [], 404);

        $pageSlug = array_pop($segments);
        $parentId = null;
        $category = null;
        foreach ($segments as $categorySlug) {
            $category = PageCategory::query()
                ->where('parent_id', $parentId)
                ->where('slug', $categorySlug)
                ->first();
            abort_if($category === null, 404);
            $parentId = $category->id;
        }

        $query = Page::query()
            ->where('slug', $pageSlug)
            ->where('status', 'published')
            ->with('category.parent.parent');
        $category === null
            ? $query->whereNull('page_category_id')
            : $query->where('page_category_id', $category->id);
        $page = $query->firstOrFail();

        return view('pages::pages.show', [
            'page' => $page,
            'safeContent' => $service->sanitizeContent((string) $page->content),
        ]);
    }

    /** @return list<array{id: int, name: string, path: string, depth: int, children: list<array>}> */
    private function categoryTree(): array
    {
        $categories = PageCategory::query()->orderBy('sort_order')->orderBy('name')->get();
        $byParent = $categories->groupBy(fn (PageCategory $category): string => (string) ($category->parent_id ?? 0));
        $build = function (?int $parentId, int $depth, string $parentPath = '') use (&$build, $byParent): array {
            $nodes = [];
            foreach ($byParent->get((string) ($parentId ?? 0), collect()) as $category) {
                $path = $parentPath === '' ? $category->name : $parentPath.' / '.$category->name;
                $nodes[] = [
                    'id' => $category->id,
                    'name' => $category->name,
                    'path' => $path,
                    'depth' => $depth,
                    'children' => $build($category->id, $depth + 1, $path),
                ];
            }

            return $nodes;
        };

        return $build(null, 0);
    }
}
