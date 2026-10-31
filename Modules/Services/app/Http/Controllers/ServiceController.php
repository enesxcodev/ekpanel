<?php

namespace Modules\Services\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Settings\Language;
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
use Modules\Services\Models\ServiceTranslation;
use Modules\Services\Services\ServiceService;
use Modules\Services\Services\ServicesLanguageService;

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
            route('admin.services.edit', ['service' => $service->id, 'lang' => Language::query()->findOrFail($request->validated('lang_id'))->code], absolute: false),
        );

        return redirect()->route('admin.services.edit', ['service' => $service->id, 'lang' => Language::query()->findOrFail($request->validated('lang_id'))->code])->with('notification', [
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
            route('admin.services.edit', ['service' => $service->id, 'lang' => Language::query()->findOrFail($request->validated('lang_id'))->code], absolute: false),
        );

        return redirect()->route('admin.services.edit', ['service' => $service->id, 'lang' => Language::query()->findOrFail($request->validated('lang_id'))->code])->with('notification', ['type' => 'success', 'message' => 'Hizmet başarıyla güncellendi.']);
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

    public function translationEdit(ServiceTranslation $translation): RedirectResponse
    {
        $this->authorize('services.update');

        return redirect()->route('admin.services.edit', ['service' => $translation->service_id, 'lang' => $translation->language->code]);
    }

    private function languageOptions(?Service $service = null): array
    {
        $languages = app(ServicesLanguageService::class)->active();
        $selectedLanguage = app(ServicesLanguageService::class)->forAdmin(request()->query('lang'));
        $translation = $selectedLanguage ? $service?->translations()->where('lang_id', $selectedLanguage->id)->first() : null;
        $translatedLanguageIds = $service?->translations()->pluck('lang_id')->all() ?? [];

        return compact('languages', 'selectedLanguage', 'translation', 'translatedLanguageIds');
    }

    private function form(ModuleFeatures $features, FeatureValues $values, ?Service $service = null): View
    {
        $service?->load('imageMedia');
        $featureForm = $features->form('Services', 'service');
        $options = $this->languageOptions($service);
        $featureFields = $values->fields($featureForm['fields'], $options['translation']?->extra_fields ?? []);
        $categories = ServiceCategory::query()->orderBy('sort_order')->orderBy('id')->get()
            ->map(fn (ServiceCategory $category): array => ['value' => $category->id, 'label' => $category->name])->all();
        $icons = collect(config('services.icons'))->map(fn (string $label, string $value): array => compact('value', 'label'))->values()->all();

        return view($service === null ? 'services::create' : 'services::edit', compact('service', 'featureForm', 'featureFields', 'categories', 'icons') + $options + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }
}
