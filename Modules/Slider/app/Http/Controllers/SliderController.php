<?php

namespace Modules\Slider\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
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

    public function create(ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('slider.create');
        $featureForm = $features->form('Slider', 'slider');
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('slider::create', compact('featureForm', 'featureFields'));
    }

    public function store(SaveSliderRequest $request, SliderService $service): RedirectResponse
    {
        $service->save($request->validated());

        return redirect()->route('admin.slider.index')->with('notification', ['type' => 'success', 'message' => 'Slider başarıyla eklendi.']);
    }

    public function edit(Slider $slider, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('slider.update');
        $featureForm = $features->form('Slider', 'slider');
        $featureFields = $values->fields($featureForm['fields'], $slider->extra_fields ?? []);

        return view('slider::edit', compact('slider', 'featureForm', 'featureFields'));
    }

    public function update(SaveSliderRequest $request, Slider $slider, SliderService $service): RedirectResponse
    {
        $service->save($request->validated(), $slider);

        return redirect()->route('admin.slider.index')->with('notification', ['type' => 'success', 'message' => 'Slider başarıyla güncellendi.']);
    }

    public function destroy(Slider $slider, SliderService $service): RedirectResponse
    {
        $this->authorize('slider.delete');
        $service->delete($slider);

        return redirect()->route('admin.slider.index')->with('notification', ['type' => 'success', 'message' => 'Slider başarıyla silindi.']);
    }
}
