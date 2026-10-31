<?php

namespace Modules\Sector\Http\Controllers;

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
use Modules\Sector\DataTables\SectorsDataTable;
use Modules\Sector\Http\Requests\SaveSectorRequest;
use Modules\Sector\Models\Sector;
use Modules\Sector\Models\SectorTranslation;
use Modules\Sector\Services\SectorLanguageService;
use Modules\Sector\Services\SectorService;

class SectorController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(SectorLanguageService $languages): View
    {
        $this->authorize('sector.view');
        $selectedLanguage = $languages->forAdmin(request()->query('lang'));
        $languages = $languages->active();

        return view('sector::index', compact('languages', 'selectedLanguage'));
    }

    public function data(SectorsDataTable $dataTable): JsonResponse
    {
        $this->authorize('sector.view');

        return $dataTable->handle(app(SectorLanguageService::class));
    }

    public function create(ModuleFeatures $features, FeatureValues $values, SectorLanguageService $languages): View
    {
        $this->authorize('sector.create');
        $selectedLanguage = $languages->forAdmin(SectorLanguageService::PrimaryCode);
        $languages = $languages->active();
        $translatedLanguageIds = [];
        $sector = new Sector(['is_active' => true]);
        $featureForm = $features->form('Sector', 'sector');
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('sector::create', compact('featureForm', 'featureFields', 'languages', 'selectedLanguage', 'translatedLanguageIds', 'sector') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(SaveSectorRequest $request, SectorService $service, NotifyService $notifications): RedirectResponse
    {
        $sector = $service->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor($request->user('admin'), 'Sektör eklendi', 'Bir sektör eklendi.', route('admin.sector.edit', ['sector' => $sector->id, 'lang' => 'tr'], absolute: false));

        return redirect()->route('admin.sector.edit', ['sector' => $sector, 'lang' => 'tr'])->with('notification', ['type' => 'success', 'message' => 'Sektör başarıyla eklendi.']);
    }

    public function edit(Sector $sector, ModuleFeatures $features, FeatureValues $values, SectorLanguageService $languages): View
    {
        $this->authorize('sector.update');
        $selectedLanguage = $languages->forAdmin(request()->query('lang'));
        $languages = $languages->active();
        $translation = $sector->translations()->with('language')->where('lang_id', $selectedLanguage?->id)->first();
        $sector->setRelation('selectedTranslation', $translation);
        $sector->load('imageMedia');
        $translatedLanguageIds = $sector->translations()->pluck('lang_id')->all();
        $featureForm = $features->form('Sector', 'sector');
        $featureFields = $values->fields($featureForm['fields'], $translation?->extra_fields ?? []);

        return view('sector::edit', compact('sector', 'translation', 'featureForm', 'featureFields', 'languages', 'selectedLanguage', 'translatedLanguageIds') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function editTranslation(SectorTranslation $translation): RedirectResponse
    {
        $this->authorize('sector.update');
        $translation->loadMissing('language');

        return redirect()->route('admin.sector.edit', ['sector' => $translation->sector_id, 'lang' => $translation->language?->code ?? 'tr']);
    }

    public function update(SaveSectorRequest $request, Sector $sector, SectorService $service, NotifyService $notifications): RedirectResponse
    {
        $sector = $service->save($request->validated(), $sector, $request->user('admin'));
        $lang = $request->input('lang_id');
        $notifications->sendToAdminsForActor($request->user('admin'), 'Sektör güncellendi', 'Bir sektör güncellendi.', route('admin.sector.edit', ['sector' => $sector->id, 'lang' => request()->query('lang', 'tr')], absolute: false));

        return redirect()->route('admin.sector.edit', ['sector' => $sector, 'lang' => Language::query()->findOrFail($lang)->code])->with('notification', ['type' => 'success', 'message' => 'Sektör başarıyla güncellendi.']);
    }

    public function destroy(Sector $sector, SectorService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('sector.delete');
        $service->delete($sector);
        $notifications->sendToAdminsForActor($request->user('admin'), 'Sektör silindi', 'Bir sektör silindi.', route('admin.sector.index', absolute: false));

        return redirect()->route('admin.sector.index')->with('notification', ['type' => 'success', 'message' => 'Sektör başarıyla silindi.']);
    }
}
