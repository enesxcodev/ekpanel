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
use Modules\Testimonials\Models\TestimonialTranslation;
use Modules\Testimonials\Services\TestimonialLanguageService;
use Modules\Testimonials\Services\TestimonialService;

class TestimonialController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(Request $request, TestimonialLanguageService $languages): View
    {
        $this->authorize('testimonials.view');

        $selectedLanguage = $languages->forAdmin($request->query('lang'));
        $activeLanguages = $languages->active();

        return view('testimonials::index', compact('selectedLanguage', 'activeLanguages'));
    }

    public function data(Request $request, TestimonialsDataTable $dataTable, TestimonialLanguageService $languages): JsonResponse
    {
        $this->authorize('testimonials.view');

        $selectedLanguage = $languages->forAdmin($request->query('lang'));

        return $dataTable->handle($selectedLanguage);
    }

    public function create(Request $request, TestimonialLanguageService $languages, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('testimonials.create');
        $featureForm = $features->form('Testimonials', 'testimonial');
        $featureFields = $values->fields($featureForm['fields'], []);

        $selectedLanguage = $languages->forAdmin($request->query('lang'));
        $activeLanguages = $languages->active();
        $translation = null;
        $testimonial = null;

        return view('testimonials::create', compact('featureForm', 'featureFields', 'selectedLanguage', 'activeLanguages', 'translation', 'testimonial') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(SaveTestimonialRequest $request, TestimonialService $service, NotifyService $notifications): RedirectResponse
    {
        $testimonial = $service->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Müşteri yorumu eklendi',
            'Bir müşteri yorumu eklendi.',
            route('admin.testimonials.edit', ['testimonial' => $testimonial->id, 'lang' => 'tr'], absolute: false),
        );

        return redirect()->route('admin.testimonials.index', ['lang' => 'tr'])->with('notification', ['type' => 'success', 'message' => 'Müşteri yorumu başarıyla eklendi.']);
    }

    public function edit(Request $request, Testimonial $testimonial, TestimonialLanguageService $languages, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('testimonials.update');
        $featureForm = $features->form('Testimonials', 'testimonial');
        $selectedLanguage = $languages->forAdmin($request->query('lang'));
        $activeLanguages = $languages->active();
        $testimonial->load(['imageMedia', 'translations' => fn ($query) => $query->where('lang_id', $selectedLanguage?->id)]);
        $translation = $testimonial->translations->first();
        $featureFields = $values->fields($featureForm['fields'], $translation?->extra_fields ?? []);

        return view('testimonials::edit', compact('testimonial', 'translation', 'featureForm', 'featureFields', 'selectedLanguage', 'activeLanguages') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function editTranslation(TestimonialTranslation $translation): RedirectResponse
    {
        return redirect()->route('admin.testimonials.edit', ['testimonial' => $translation->testimonial_id, 'lang' => $translation->language()->value('code')]);
    }

    public function update(SaveTestimonialRequest $request, Testimonial $testimonial, TestimonialService $service, NotifyService $notifications): RedirectResponse
    {
        $testimonial = $service->save($request->validated(), $testimonial, $request->user('admin'));
        $language = app(TestimonialLanguageService::class)->forAdmin($request->input('lang'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Müşteri yorumu güncellendi',
            'Bir müşteri yorumu güncellendi.',
            route('admin.testimonials.edit', ['testimonial' => $testimonial->id, 'lang' => $language?->code], absolute: false),
        );

        return redirect()->route('admin.testimonials.index', ['lang' => $language?->code])->with('notification', ['type' => 'success', 'message' => 'Müşteri yorumu başarıyla güncellendi.']);
    }

    public function destroy(Testimonial $testimonial, TestimonialService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('testimonials.delete');
        $service->delete($testimonial);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Müşteri yorumu silindi',
            'Bir müşteri yorumu silindi.',
            route('admin.testimonials.index', ['lang' => $request->query('lang', 'tr')], absolute: false),
        );

        return redirect()->route('admin.testimonials.index', ['lang' => $request->query('lang', 'tr')])->with('notification', ['type' => 'success', 'message' => 'Müşteri yorumu başarıyla silindi.']);
    }
}
