<?php

namespace Modules\Services\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Services\DataTables\ServiceCategoriesDataTable;
use Modules\Services\Http\Requests\StoreServiceCategoryRequest;
use Modules\Services\Http\Requests\UpdateServiceCategoryRequest;
use Modules\Services\Models\ServiceCategory;
use Modules\Services\Services\ServiceCategoryService;

class ServiceCategoryController extends Controller
{
    public function __construct(private MediaService $media) {}

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

        return view('services::categories.create', compact('featureForm', 'featureFields') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(StoreServiceCategoryRequest $request, ServiceCategoryService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Hizmet kategorisi eklendi',
            'Bir hizmet kategorisi eklendi.',
            route('admin.services.categories.edit', ['category' => $category->id], absolute: false),
        );

        return redirect()->route('admin.services.categories.index')->with('notification', [
            'type' => 'success', 'message' => 'Hizmet kategorisi başarıyla eklendi.',
        ]);
    }

    public function edit(ServiceCategory $category, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('services.categories.update');

        $featureForm = $features->form('Services', 'category');
        $featureFields = $values->fields($featureForm['fields'], $category->extra_fields ?? []);

        return view('services::categories.edit', compact('category', 'featureForm', 'featureFields') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $category, ServiceCategoryService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->save($request->validated(), $category, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Hizmet kategorisi güncellendi',
            'Bir hizmet kategorisi güncellendi.',
            route('admin.services.categories.edit', ['category' => $category->id], absolute: false),
        );

        return redirect()->route('admin.services.categories.index')->with('notification', [
            'type' => 'success', 'message' => 'Hizmet kategorisi başarıyla güncellendi.',
        ]);
    }

    public function destroy(ServiceCategory $category, ServiceCategoryService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('services.categories.delete');
        $service->delete($category);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Hizmet kategorisi silindi',
            'Bir hizmet kategorisi silindi.',
            route('admin.services.categories.index', absolute: false),
        );

        return redirect()->route('admin.services.categories.index')->with('notification', [
            'type' => 'success', 'message' => 'Hizmet kategorisi başarıyla silindi.',
        ]);
    }
}
