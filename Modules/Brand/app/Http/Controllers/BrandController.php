<?php

namespace Modules\Brand\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Brand\DataTables\BrandsDataTable;
use Modules\Brand\Http\Requests\SaveBrandRequest;
use Modules\Brand\Models\Brand;
use Modules\Brand\Services\BrandService;

class BrandController extends Controller
{
    public function index(): View
    {
        $this->authorize('brand.view');

        return view('brand::index');
    }

    public function create(ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('brand.create');
        $featureForm = $features->form('Brand', 'brand');
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('brand::create', compact('featureForm', 'featureFields'));
    }

    public function data(BrandsDataTable $dataTable): JsonResponse
    {
        $this->authorize('brand.view');

        return $dataTable->handle();
    }

    public function store(SaveBrandRequest $request, BrandService $service): RedirectResponse
    {
        $service->save($request->validated());

        return redirect()->route('admin.brand.index')->with('notification', ['type' => 'success', 'message' => 'Marka başarıyla eklendi.']);
    }

    public function edit(Brand $brand, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('brand.update');
        $featureForm = $features->form('Brand', 'brand');
        $featureFields = $values->fields($featureForm['fields'], $brand->extra_fields ?? []);

        return view('brand::edit', compact('brand', 'featureForm', 'featureFields'));
    }

    public function update(SaveBrandRequest $request, Brand $brand, BrandService $service): RedirectResponse
    {
        $service->save($request->validated(), $brand);

        return redirect()->route('admin.brand.index')->with('notification', ['type' => 'success', 'message' => 'Marka başarıyla güncellendi.']);
    }

    public function destroy(Brand $brand, BrandService $service): RedirectResponse
    {
        $this->authorize('brand.delete');
        $service->delete($brand);

        return redirect()->route('admin.brand.index')->with('notification', ['type' => 'success', 'message' => 'Marka başarıyla silindi.']);
    }
}
