<?php

namespace Modules\Testimonials\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Testimonials\DataTables\TestimonialsDataTable;
use Modules\Testimonials\Http\Requests\SaveTestimonialRequest;
use Modules\Testimonials\Models\Testimonial;
use Modules\Testimonials\Services\TestimonialService;

class TestimonialController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(): View
    {
        $this->authorize('testimonials.view');

        return view('testimonials::index');
    }

    public function data(TestimonialsDataTable $dataTable): JsonResponse
    {
        $this->authorize('testimonials.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('testimonials.create');
        $featureForm = $features->form('Testimonials', 'testimonial');
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('testimonials::create', compact('featureForm', 'featureFields') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(SaveTestimonialRequest $request, TestimonialService $service, NotifyService $notifications): RedirectResponse
    {
        $testimonial = $service->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Müşteri yorumu eklendi',
            'Bir müşteri yorumu eklendi.',
            route('admin.testimonials.edit', ['testimonial' => $testimonial->id], absolute: false),
        );

        return redirect()->route('admin.testimonials.index')->with('notification', ['type' => 'success', 'message' => 'Müşteri yorumu başarıyla eklendi.']);
    }

    public function edit(Testimonial $testimonial, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('testimonials.update');
        $featureForm = $features->form('Testimonials', 'testimonial');
        $testimonial->load('imageMedia');
        $featureFields = $values->fields($featureForm['fields'], $testimonial->extra_fields ?? []);

        return view('testimonials::edit', compact('testimonial', 'featureForm', 'featureFields') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function update(SaveTestimonialRequest $request, Testimonial $testimonial, TestimonialService $service, NotifyService $notifications): RedirectResponse
    {
        $testimonial = $service->save($request->validated(), $testimonial, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Müşteri yorumu güncellendi',
            'Bir müşteri yorumu güncellendi.',
            route('admin.testimonials.edit', ['testimonial' => $testimonial->id], absolute: false),
        );

        return redirect()->route('admin.testimonials.index')->with('notification', ['type' => 'success', 'message' => 'Müşteri yorumu başarıyla güncellendi.']);
    }

    public function destroy(Testimonial $testimonial, TestimonialService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('testimonials.delete');
        $service->delete($testimonial);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Müşteri yorumu silindi',
            'Bir müşteri yorumu silindi.',
            route('admin.testimonials.index', absolute: false),
        );

        return redirect()->route('admin.testimonials.index')->with('notification', ['type' => 'success', 'message' => 'Müşteri yorumu başarıyla silindi.']);
    }
}
