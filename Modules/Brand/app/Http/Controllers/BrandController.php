<?php

namespace Modules\Brand\Http\Controllers;

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
use Modules\Brand\DataTables\BrandsDataTable;
use Modules\Brand\Http\Requests\SaveBrandRequest;
use Modules\Brand\Models\Brand;
use Modules\Brand\Services\BrandLanguageService;
use Modules\Brand\Services\BrandService;

class BrandController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(): View
    {
        $this->authorize('brand.view');

        return view('brand::index');
    }

    public function create(ModuleFeatures $features, FeatureValues $values, BrandLanguageService $languages, Request $request): View
    {
        $this->authorize('brand.create');
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->forAdmin($request->query('lang', 'tr'));
        if ($selectedLanguage?->code !== 'tr') {
            abort(404);
        }
        $featureForm = $features->form('Brand', 'brand');
        $featureFields = $values->fields($featureForm['fields'], []);

        $translation = null;
        $translatedLanguageIds = [];

        return view('brand::create', compact('featureForm', 'featureFields', 'activeLanguages', 'selectedLanguage', 'translation', 'translatedLanguageIds') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function data(BrandsDataTable $dataTable): JsonResponse
    {
        $this->authorize('brand.view');

        return $dataTable->handle();
    }

    public function store(SaveBrandRequest $request, BrandService $service, NotifyService $notifications): RedirectResponse
    {
        $brand = $service->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Marka eklendi',
            'Bir marka eklendi.',
            route('admin.brand.edit', ['brand' => $brand->id], absolute: false),
        );

        return redirect()->route('admin.brand.edit', ['brand' => $brand->id, 'lang' => 'tr'])->with('notification', ['type' => 'success', 'message' => 'Marka başarıyla eklendi.']);
    }

    public function edit(Brand $brand, ModuleFeatures $features, FeatureValues $values, BrandLanguageService $languages, Request $request): View
    {
        $this->authorize('brand.update');
        $featureForm = $features->form('Brand', 'brand');
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->forAdmin($request->query('lang'));
        $brand->load(['imageMedia', 'translations']);
        $translation = $brand->translations->firstWhere('lang_id', $selectedLanguage?->id);
        $translatedLanguageIds = $brand->translations->pluck('lang_id')->all();
        $featureFields = $values->fields($featureForm['fields'], $translation?->extra_fields ?? []);

        return view('brand::edit', compact('brand', 'featureForm', 'featureFields', 'activeLanguages', 'selectedLanguage', 'translation', 'translatedLanguageIds') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function update(SaveBrandRequest $request, Brand $brand, BrandService $service, NotifyService $notifications): RedirectResponse
    {
        $brand = $service->save($request->validated(), $brand, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Marka güncellendi',
            'Bir marka güncellendi.',
            route('admin.brand.edit', ['brand' => $brand->id], absolute: false),
        );

        $code = Language::query()->findOrFail($request->validated('lang_id'))->code;

        return redirect()->route('admin.brand.edit', ['brand' => $brand->id, 'lang' => $code])->with('notification', ['type' => 'success', 'message' => 'Marka başarıyla güncellendi.']);
    }

    public function destroy(Brand $brand, BrandService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('brand.delete');
        $service->delete($brand);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Marka silindi',
            'Bir marka silindi.',
            route('admin.brand.index', absolute: false),
        );

        return redirect()->route('admin.brand.index')->with('notification', ['type' => 'success', 'message' => 'Marka başarıyla silindi.']);
    }
}
