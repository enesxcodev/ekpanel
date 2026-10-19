<?php

namespace Modules\Sector\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Sector\DataTables\SectorsDataTable;
use Modules\Sector\Http\Requests\SaveSectorRequest;
use Modules\Sector\Models\Sector;
use Modules\Sector\Services\SectorService;

class SectorController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(): View
    {
        $this->authorize('sector.view');

        return view('sector::index');
    }

    public function data(SectorsDataTable $dataTable): JsonResponse
    {
        $this->authorize('sector.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('sector.create');
        $featureForm = $features->form('Sector', 'sector');
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('sector::create', compact('featureForm', 'featureFields') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(SaveSectorRequest $request, SectorService $service, NotifyService $notifications): RedirectResponse
    {
        $sector = $service->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Sektör eklendi',
            'Bir sektör eklendi.',
            route('admin.sector.edit', ['sector' => $sector->id], absolute: false),
        );

        return redirect()->route('admin.sector.index')->with('notification', ['type' => 'success', 'message' => 'Sektör başarıyla eklendi.']);
    }

    public function edit(Sector $sector, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('sector.update');
        $featureForm = $features->form('Sector', 'sector');
        $sector->load('imageMedia');
        $featureFields = $values->fields($featureForm['fields'], $sector->extra_fields ?? []);

        return view('sector::edit', compact('sector', 'featureForm', 'featureFields') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function update(SaveSectorRequest $request, Sector $sector, SectorService $service, NotifyService $notifications): RedirectResponse
    {
        $sector = $service->save($request->validated(), $sector, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Sektör güncellendi',
            'Bir sektör güncellendi.',
            route('admin.sector.edit', ['sector' => $sector->id], absolute: false),
        );

        return redirect()->route('admin.sector.index')->with('notification', ['type' => 'success', 'message' => 'Sektör başarıyla güncellendi.']);
    }

    public function destroy(Sector $sector, SectorService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('sector.delete');
        $service->delete($sector);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Sektör silindi',
            'Bir sektör silindi.',
            route('admin.sector.index', absolute: false),
        );

        return redirect()->route('admin.sector.index')->with('notification', ['type' => 'success', 'message' => 'Sektör başarıyla silindi.']);
    }
}
