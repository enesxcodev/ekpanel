<?php

namespace Modules\Sector\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

    public function store(SaveSectorRequest $request, SectorService $service): RedirectResponse
    {
        $service->save($request->validated(), null, $request->user('admin'));

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

    public function update(SaveSectorRequest $request, Sector $sector, SectorService $service): RedirectResponse
    {
        $service->save($request->validated(), $sector, $request->user('admin'));

        return redirect()->route('admin.sector.index')->with('notification', ['type' => 'success', 'message' => 'Sektör başarıyla güncellendi.']);
    }

    public function destroy(Sector $sector, SectorService $service): RedirectResponse
    {
        $this->authorize('sector.delete');
        $service->delete($sector);

        return redirect()->route('admin.sector.index')->with('notification', ['type' => 'success', 'message' => 'Sektör başarıyla silindi.']);
    }
}
