<?php

namespace Modules\Slider\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Slider\DataTables\SlidersDataTable;
use Modules\Slider\Http\Requests\SaveSliderRequest;
use Modules\Slider\Models\Slider;
use Modules\Slider\Services\SliderService;

class SliderController extends Controller
{
    public function index(): View
    {
        $this->authorize('slider.view');

        return view('slider::index');
    }

    public function data(SlidersDataTable $dataTable): JsonResponse
    {
        $this->authorize('slider.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values, MediaService $media): View
    {
        $this->authorize('slider.create');
        $featureForm = $features->form('Slider', 'slider');
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('slider::create', ['featureForm' => $featureForm, 'featureFields' => $featureFields, 'mediaConfig' => $media->configuration(auth('admin')->user())]);
    }

    public function store(SaveSliderRequest $request, SliderService $service, NotifyService $notifications): RedirectResponse
    {
        $slider = $service->save($request->validated(), $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Slider eklendi',
            'Bir slider eklendi.',
            route('admin.slider.edit', ['slider' => $slider->id], absolute: false),
        );

        return redirect()->route('admin.slider.index')->with('notification', ['type' => 'success', 'message' => 'Slider başarıyla eklendi.']);
    }

    public function edit(Slider $slider, ModuleFeatures $features, FeatureValues $values, MediaService $media): View
    {
        $this->authorize('slider.update');
        $slider->load('imageMedia');
        $featureForm = $features->form('Slider', 'slider');
        $featureFields = $values->fields($featureForm['fields'], $slider->extra_fields ?? []);

        return view('slider::edit', ['slider' => $slider, 'featureForm' => $featureForm, 'featureFields' => $featureFields, 'mediaConfig' => $media->configuration(auth('admin')->user())]);
    }

    public function update(SaveSliderRequest $request, Slider $slider, SliderService $service, NotifyService $notifications): RedirectResponse
    {
        $slider = $service->save($request->validated(), $request->user('admin'), $slider);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Slider güncellendi',
            'Bir slider güncellendi.',
            route('admin.slider.edit', ['slider' => $slider->id], absolute: false),
        );

        return redirect()->route('admin.slider.index')->with('notification', ['type' => 'success', 'message' => 'Slider başarıyla güncellendi.']);
    }

    public function destroy(Slider $slider, SliderService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('slider.delete');
        $service->delete($slider);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Slider silindi',
            'Bir slider silindi.',
            route('admin.slider.index', absolute: false),
        );

        return redirect()->route('admin.slider.index')->with('notification', ['type' => 'success', 'message' => 'Slider başarıyla silindi.']);
    }
}
