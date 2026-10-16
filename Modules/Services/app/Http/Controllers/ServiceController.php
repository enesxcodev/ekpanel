<?php

namespace Modules\Services\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Services\DataTables\ServicesDataTable;
use Modules\Services\Http\Requests\StoreServiceRequest;
use Modules\Services\Models\ServiceCategory;
use Modules\Services\Services\ServiceService;

class ServiceController extends Controller
{
    public function index(): View
    {
        $this->authorize('services.view');

        return view('services::index');
    }

    public function data(ServicesDataTable $dataTable): JsonResponse
    {
        $this->authorize('services.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('services.create');
        $featureForm = $features->form('Services', 'service');
        $featureFields = $values->fields($featureForm['fields'], []);
        $categories = ServiceCategory::query()->orderBy('name')->get(['id', 'name'])
            ->map(fn (ServiceCategory $category): array => ['value' => $category->id, 'label' => $category->name])->all();
        $icons = collect(config('services.icons'))->map(fn (string $label, string $value): array => compact('value', 'label'))->values()->all();

        return view('services::create', compact('featureForm', 'featureFields', 'categories', 'icons'));
    }

    public function store(StoreServiceRequest $request, ServiceService $service): RedirectResponse
    {
        $service->create($request->validated());

        return redirect()->route('admin.services.index')->with('notification', [
            'type' => 'success', 'message' => 'Hizmet başarıyla eklendi.',
        ]);
    }
}
