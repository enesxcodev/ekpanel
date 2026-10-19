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
use Modules\Services\DataTables\ServicesDataTable;
use Modules\Services\Http\Requests\StoreServiceRequest;
use Modules\Services\Http\Requests\UpdateServiceRequest;
use Modules\Services\Models\Service;
use Modules\Services\Models\ServiceCategory;
use Modules\Services\Services\ServiceService;

class ServiceController extends Controller
{
    public function __construct(private MediaService $media) {}

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

        return $this->form($features, $values);
    }

    public function store(StoreServiceRequest $request, ServiceService $service, NotifyService $notifications): RedirectResponse
    {
        $service = $service->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Hizmet eklendi',
            'Bir hizmet eklendi.',
            route('admin.services.edit', ['service' => $service->id], absolute: false),
        );

        return redirect()->route('admin.services.index')->with('notification', [
            'type' => 'success', 'message' => 'Hizmet başarıyla eklendi.',
        ]);
    }

    public function edit(Service $service, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('services.update');

        return $this->form($features, $values, $service);
    }

    public function update(UpdateServiceRequest $request, Service $service, ServiceService $manager, NotifyService $notifications): RedirectResponse
    {
        $service = $manager->save($request->validated(), $service, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Hizmet güncellendi',
            'Bir hizmet güncellendi.',
            route('admin.services.edit', ['service' => $service->id], absolute: false),
        );

        return redirect()->route('admin.services.index')->with('notification', ['type' => 'success', 'message' => 'Hizmet başarıyla güncellendi.']);
    }

    public function destroy(Service $service, ServiceService $manager, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('services.delete');
        $manager->delete($service);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Hizmet silindi',
            'Bir hizmet silindi.',
            route('admin.services.index', absolute: false),
        );

        return redirect()->route('admin.services.index')->with('notification', ['type' => 'success', 'message' => 'Hizmet başarıyla silindi.']);
    }

    private function form(ModuleFeatures $features, FeatureValues $values, ?Service $service = null): View
    {
        $service?->load('imageMedia');
        $featureForm = $features->form('Services', 'service');
        $featureFields = $values->fields($featureForm['fields'], $service?->extra_fields ?? []);
        $categories = ServiceCategory::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name'])
            ->map(fn (ServiceCategory $category): array => ['value' => $category->id, 'label' => $category->name])->all();
        $icons = collect(config('services.icons'))->map(fn (string $label, string $value): array => compact('value', 'label'))->values()->all();

        return view($service === null ? 'services::create' : 'services::edit', compact('service', 'featureForm', 'featureFields', 'categories', 'icons') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }
}
