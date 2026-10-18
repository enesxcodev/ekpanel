<?php

namespace Modules\Brand\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Brand\DataTables\BrandsDataTable;
use Modules\Brand\Http\Requests\SaveBrandRequest;
use Modules\Brand\Models\Brand;
use Modules\Brand\Services\BrandService;

class BrandController extends Controller
{
    public function __construct(private MediaService $media) {}

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

        return view('brand::create', compact('featureForm', 'featureFields') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function data(BrandsDataTable $dataTable): JsonResponse
    {
        $this->authorize('brand.view');

        return $dataTable->handle();
    }

    public function store(SaveBrandRequest $request, BrandService $service): RedirectResponse
    {
        $service->save($request->validated(), null, $request->user('admin'));

        return redirect()->route('admin.brand.index')->with('notification', ['type' => 'success', 'message' => 'Marka başarıyla eklendi.']);
    }

    public function edit(Brand $brand, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('brand.update');
        $featureForm = $features->form('Brand', 'brand');
        $brand->load('imageMedia');
        $featureFields = $values->fields($featureForm['fields'], $brand->extra_fields ?? []);

        return view('brand::edit', compact('brand', 'featureForm', 'featureFields') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function update(SaveBrandRequest $request, Brand $brand, BrandService $service): RedirectResponse
    {
        $service->save($request->validated(), $brand, $request->user('admin'));

        return redirect()->route('admin.brand.index')->with('notification', ['type' => 'success', 'message' => 'Marka başarıyla güncellendi.']);
    }

    public function destroy(Brand $brand, BrandService $service): RedirectResponse
    {
        $this->authorize('brand.delete');
        $service->delete($brand);

        return redirect()->route('admin.brand.index')->with('notification', ['type' => 'success', 'message' => 'Marka başarıyla silindi.']);
    }
}
