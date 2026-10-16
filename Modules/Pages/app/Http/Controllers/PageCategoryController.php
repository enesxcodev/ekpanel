<?php

namespace Modules\Pages\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Pages\DataTables\PageCategoriesDataTable;
use Modules\Pages\Http\Requests\SavePageCategoryRequest;
use Modules\Pages\Models\PageCategory;
use Modules\Pages\Services\PageCategoryService;

class PageCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('pages.categories.view');

        return view('pages::categories.index');
    }

    public function data(PageCategoriesDataTable $dataTable): JsonResponse
    {
        $this->authorize('pages.categories.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('pages.categories.create');
        $featureForm = $features->form('Pages', 'category');
        $featureFields = $values->fields($featureForm['fields'], []);
        $categoryOptions = $this->categoryOptions();

        return view('pages::categories.create', compact('featureForm', 'featureFields', 'categoryOptions'));
    }

    public function store(SavePageCategoryRequest $request, PageCategoryService $service): RedirectResponse
    {
        $service->save($request->validated());

        return redirect()->route('admin.pages.categories.index')->with('notification', ['type' => 'success', 'message' => 'Sayfa kategorisi başarıyla eklendi.']);
    }

    public function edit(PageCategory $category, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('pages.categories.update');
        $featureForm = $features->form('Pages', 'category');
        $featureFields = $values->fields($featureForm['fields'], $category->extra_fields ?? []);
        $categoryOptions = $this->categoryOptions($category);

        return view('pages::categories.edit', compact('category', 'featureForm', 'featureFields', 'categoryOptions'));
    }

    public function update(SavePageCategoryRequest $request, PageCategory $category, PageCategoryService $service): RedirectResponse
    {
        $service->save($request->validated(), $category);

        return redirect()->route('admin.pages.categories.index')->with('notification', ['type' => 'success', 'message' => 'Sayfa kategorisi başarıyla güncellendi.']);
    }

    public function destroy(PageCategory $category, PageCategoryService $service): RedirectResponse
    {
        $this->authorize('pages.categories.delete');
        $deleted = $service->delete($category);

        return redirect()->route('admin.pages.categories.index')->with('notification', [
            'type' => $deleted ? 'success' : 'error',
            'message' => $deleted ? 'Sayfa kategorisi başarıyla silindi.' : 'Sayfa veya alt kategori içeren kategori silinemez.',
        ]);
    }

    /** @return list<array{value: int, label: string}> */
    private function categoryOptions(?PageCategory $excluded = null): array
    {
        $excludedIds = $excluded === null ? [] : [$excluded->id, ...$excluded->descendantIds()];
        $categories = PageCategory::query()->whereNotIn('id', $excludedIds)->orderBy('sort_order')->orderBy('name')->get();
        $byParent = $categories->groupBy(fn (PageCategory $category): string => (string) ($category->parent_id ?? 0));
        $options = [];
        $append = function (?int $parentId, int $depth) use (&$append, &$options, $byParent): void {
            foreach ($byParent->get((string) ($parentId ?? 0), collect()) as $category) {
                $options[] = ['value' => $category->id, 'label' => str_repeat('— ', $depth).$category->name];
                $append($category->id, $depth + 1);
            }
        };
        $append(null, 0);

        return $options;
    }
}
