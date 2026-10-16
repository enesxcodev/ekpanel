<?php

namespace Modules\Services\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Services\DataTables\ServiceCategoriesDataTable;
use Modules\Services\Http\Requests\StoreServiceCategoryRequest;
use Modules\Services\Http\Requests\UpdateServiceCategoryRequest;
use Modules\Services\Models\ServiceCategory;
use Modules\Services\Services\ServiceCategoryService;

class ServiceCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('services.categories.view');

        return view('services::categories.index');
    }

    public function data(ServiceCategoriesDataTable $dataTable): JsonResponse
    {
        $this->authorize('services.categories.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('services.categories.create');

        $featureForm = $features->form('Services', 'category');
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('services::categories.create', compact('featureForm', 'featureFields'));
    }

    public function store(StoreServiceCategoryRequest $request, ServiceCategoryService $service): RedirectResponse
    {
        $service->save($request->validated());

        return redirect()->route('admin.services.categories.index')->with('notification', [
            'type' => 'success', 'message' => 'Hizmet kategorisi başarıyla eklendi.',
        ]);
    }

    public function edit(ServiceCategory $category, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('services.categories.update');

        $featureForm = $features->form('Services', 'category');
        $featureFields = $values->fields($featureForm['fields'], $category->extra_fields ?? []);

        return view('services::categories.edit', compact('category', 'featureForm', 'featureFields'));
    }

    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $category, ServiceCategoryService $service): RedirectResponse
    {
        $service->save($request->validated(), $category);

        return redirect()->route('admin.services.categories.index')->with('notification', [
            'type' => 'success', 'message' => 'Hizmet kategorisi başarıyla güncellendi.',
        ]);
    }

    public function destroy(ServiceCategory $category, ServiceCategoryService $service): RedirectResponse
    {
        $this->authorize('services.categories.delete');
        $service->delete($category);

        return redirect()->route('admin.services.categories.index')->with('notification', [
            'type' => 'success', 'message' => 'Hizmet kategorisi başarıyla silindi.',
        ]);
    }
}
