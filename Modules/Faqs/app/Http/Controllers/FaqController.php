<?php

namespace Modules\Faqs\Http\Controllers;

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
use Modules\Faqs\DataTables\FaqsDataTable;
use Modules\Faqs\Http\Requests\SaveFaqRequest;
use Modules\Faqs\Models\Faq;
use Modules\Faqs\Models\FaqCategory;
use Modules\Faqs\Models\FaqTranslation;
use Modules\Faqs\Services\FaqLanguageService;
use Modules\Faqs\Services\FaqService;

class FaqController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(FaqLanguageService $languages): View
    {
        $this->authorize('faqs.view');
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->forAdmin(request()->query('lang'));

        return view('faqs::index', ['selectedLanguage' => $selectedLanguage, 'languages' => $activeLanguages, 'translatedLanguageIds' => []]);
    }

    public function data(FaqsDataTable $dataTable): JsonResponse
    {
        $this->authorize('faqs.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values, FaqLanguageService $languages): View|RedirectResponse
    {
        $this->authorize('faqs.create');
        $options = $this->formOptions($languages);
        if ($options['languages']->isEmpty()) {
            return redirect()->route('admin.settings.index', ['section' => 'languages'])->with('notification', ['type' => 'warning', 'message' => 'SSS kaydetmek için önce Ayarlar > Diller bölümünde etkin bir dil ekleyin.']);
        }
        $featureForm = $features->form('Faqs', 'faq');
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('faqs::create', compact('featureForm', 'featureFields') + $options + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(SaveFaqRequest $request, FaqService $service, NotifyService $notifications): RedirectResponse
    {
        $faq = $service->save($request->validated(), null, $request->user('admin'));
        $language = Language::query()->findOrFail($request->validated('lang_id'));
        $notifications->sendToAdminsForActor($request->user('admin'), 'Soru eklendi', 'Bir soru eklendi.', route('admin.faqs.edit', ['faq' => $faq->id, 'lang' => $language->code], absolute: false));

        return redirect()->route('admin.faqs.edit', ['faq' => $faq->id, 'lang' => $language->code])->with('notification', ['type' => 'success', 'message' => 'Soru başarıyla eklendi.']);
    }

    public function edit(Faq $faq, ModuleFeatures $features, FeatureValues $values, FaqLanguageService $languages): View|RedirectResponse
    {
        $this->authorize('faqs.update');
        $options = $this->formOptions($languages, $faq);
        if ($options['languages']->isEmpty()) {
            return redirect()->route('admin.settings.index', ['section' => 'languages'])->with('notification', ['type' => 'warning', 'message' => 'SSS düzenlemek için önce Ayarlar > Diller bölümünde etkin bir dil ekleyin.']);
        }
        $featureForm = $features->form('Faqs', 'faq');
        $featureFields = $values->fields($featureForm['fields'], $options['translation']?->extra_fields ?? []);
        $categoryOptions = $this->categoryOptions($options['selectedLanguage']?->id);

        return view('faqs::edit', compact('faq', 'featureForm', 'featureFields', 'categoryOptions') + $options + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function update(SaveFaqRequest $request, Faq $faq, FaqService $service, NotifyService $notifications): RedirectResponse
    {
        $faq = $service->save($request->validated(), $faq, $request->user('admin'));
        $language = Language::query()->findOrFail($request->validated('lang_id'));
        $notifications->sendToAdminsForActor($request->user('admin'), 'Soru güncellendi', 'Bir soru güncellendi.', route('admin.faqs.edit', ['faq' => $faq->id, 'lang' => $language->code], absolute: false));

        return redirect()->route('admin.faqs.edit', ['faq' => $faq->id, 'lang' => $language->code])->with('notification', ['type' => 'success', 'message' => 'Soru başarıyla güncellendi.']);
    }

    public function translationEdit(FaqTranslation $translation): RedirectResponse
    {
        $this->authorize('faqs.update');

        return redirect()->route('admin.faqs.edit', ['faq' => $translation->faq_id, 'lang' => $translation->language->code]);
    }

    public function destroy(Faq $faq, FaqService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('faqs.delete');
        $service->delete($faq);
        $notifications->sendToAdminsForActor($request->user('admin'), 'Soru silindi', 'Bir soru silindi.', route('admin.faqs.index', absolute: false));

        return redirect()->route('admin.faqs.index', array_filter(['lang' => $request->query('lang')]))->with('notification', ['type' => 'success', 'message' => 'Soru başarıyla silindi.']);
    }

    /** @return array<string, mixed> */
    private function formOptions(FaqLanguageService $languageService, ?Faq $faq = null): array
    {
        $languages = $languageService->active();
        $selectedLanguage = $languageService->forAdmin(request()->query('lang'));
        $translation = $selectedLanguage ? $faq?->translations()->where('lang_id', $selectedLanguage->id)->first() : null;

        return ['languages' => $languages, 'selectedLanguage' => $selectedLanguage, 'translation' => $translation, 'translatedLanguageIds' => $faq?->translations()->pluck('lang_id')->map(fn ($id): int => (int) $id)->all() ?? [], 'categoryOptions' => $this->categoryOptions($selectedLanguage?->id)];
    }

    /** @return list<array{value: int, label: string}> */
    private function categoryOptions(?int $languageId): array
    {
        return FaqCategory::query()->with(['translations' => fn ($query) => $query->where('lang_id', $languageId ?? 0)])->get()->map(function (FaqCategory $category): array {
            $name = $category->translations->first()?->name ?? 'Çeviri eksik (#'.$category->id.')';

            return ['value' => $category->id, 'label' => $name];
        })->sortBy('label', SORT_NATURAL | SORT_FLAG_CASE)->values()->all();
    }
}
