<?php

namespace Modules\Popup\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Popup\DataTables\PopupsDataTable;
use Modules\Popup\Http\Requests\SavePopupRequest;
use Modules\Popup\Models\Popup;
use Modules\Popup\Services\PopupService;

class PopupController extends Controller
{
    public function index(): View
    {
        $this->authorize('popup.view');

        return view('popup::index');
    }

    public function data(PopupsDataTable $dataTable): JsonResponse
    {
        $this->authorize('popup.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values, MediaService $media): View
    {
        $this->authorize('popup.create');
        $featureForm = $features->form('Popup', 'popup');
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('popup::create', ['featureForm' => $featureForm, 'featureFields' => $featureFields, 'mediaConfig' => $media->configuration(auth('admin')->user())]);
    }

    public function store(SavePopupRequest $request, PopupService $service, NotifyService $notifications): RedirectResponse
    {
        $popup = $service->save($request->validated(), $request->user('admin'));
        $notifications->sendToAdminsForActor($request->user('admin'), 'Pop-up eklendi', 'Yeni pop-up kaydı eklendi.', route('admin.popup.edit', $popup, absolute: false));

        return redirect()->route('admin.popup.index')->with('notification', ['type' => 'success', 'message' => 'Popup başarıyla eklendi.']);
    }

    public function edit(Popup $popup, ModuleFeatures $features, FeatureValues $values, MediaService $media): View
    {
        $this->authorize('popup.update');
        $popup->load('imageMedia');
        $featureForm = $features->form('Popup', 'popup');
        $featureFields = $values->fields($featureForm['fields'], $popup->extra_fields ?? []);

        return view('popup::edit', ['popup' => $popup, 'featureForm' => $featureForm, 'featureFields' => $featureFields, 'mediaConfig' => $media->configuration(auth('admin')->user())]);
    }

    public function update(SavePopupRequest $request, Popup $popup, PopupService $service, NotifyService $notifications): RedirectResponse
    {
        $popup = $service->save($request->validated(), $request->user('admin'), $popup);
        $notifications->sendToAdminsForActor($request->user('admin'), 'Pop-up güncellendi', 'Pop-up kaydı güncellendi.', route('admin.popup.edit', $popup, absolute: false));

        return redirect()->route('admin.popup.index')->with('notification', ['type' => 'success', 'message' => 'Popup başarıyla güncellendi.']);
    }

    public function destroy(Popup $popup, PopupService $service, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('popup.delete');
        $service->delete($popup);
        $notifications->sendToAdminsForActor(auth('admin')->user(), 'Pop-up silindi', 'Pop-up kaydı silindi.', route('admin.popup.index', absolute: false));

        return redirect()->route('admin.popup.index')->with('notification', ['type' => 'success', 'message' => 'Popup başarıyla silindi.']);
    }
}
