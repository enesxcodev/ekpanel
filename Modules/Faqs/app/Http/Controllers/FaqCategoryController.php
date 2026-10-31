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
use Modules\Faqs\DataTables\FaqCategoriesDataTable;
use Modules\Faqs\Http\Requests\SaveFaqCategoryRequest;
use Modules\Faqs\Models\FaqCategory;
use Modules\Faqs\Models\FaqCategoryTranslation;
use Modules\Faqs\Services\FaqCategoryService;
use Modules\Faqs\Services\FaqLanguageService;

class FaqCategoryController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(FaqLanguageService $languages): View
    {
        $this->authorize('faqs.categories.view');
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->forAdmin(request()->query('lang'));

        return view('faqs::categories.index', ['selectedLanguage' => $selectedLanguage, 'languages' => $activeLanguages, 'translatedLanguageIds' => []]);
    }

    public function data(FaqCategoriesDataTable $dataTable): JsonResponse
    {
        $this->authorize('faqs.categories.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values, FaqLanguageService $languages): View|RedirectResponse
    {
        $this->authorize('faqs.categories.create');
        $options = $this->formOptions($languages);
        if ($options['languages']->isEmpty()) {
            return redirect()->route('admin.settings.index', ['section' => 'languages'])->with('notification', ['type' => 'warning', 'message' => 'SSS kategorisi kaydetmek için önce Ayarlar > Diller bölümünde etkin bir dil ekleyin.']);
        }
        $featureForm = $features->form('Faqs', 'category');
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('faqs::categories.create', compact('featureForm', 'featureFields') + $options + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(SaveFaqCategoryRequest $request, FaqCategoryService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->save($request->validated(), null, $request->user('admin'));
        $language = Language::query()->findOrFail($request->validated('lang_id'));
        $notifications->sendToAdminsForActor($request->user('admin'), 'SSS kategorisi eklendi', 'Bir SSS kategorisi eklendi.', route('admin.faqs.categories.edit', ['category' => $category->id, 'lang' => $language->code], absolute: false));

        return redirect()->route('admin.faqs.categories.edit', ['category' => $category->id, 'lang' => $language->code])->with('notification', ['type' => 'success', 'message' => 'Kategori başarıyla eklendi.']);
    }

    public function edit(FaqCategory $category, ModuleFeatures $features, FeatureValues $values, FaqLanguageService $languages): View|RedirectResponse
    {
        $this->authorize('faqs.categories.update');
        $options = $this->formOptions($languages, $category);
        if ($options['languages']->isEmpty()) {
            return redirect()->route('admin.settings.index', ['section' => 'languages'])->with('notification', ['type' => 'warning', 'message' => 'SSS kategorisi düzenlemek için önce Ayarlar > Diller bölümünde etkin bir dil ekleyin.']);
        }
        $featureForm = $features->form('Faqs', 'category');
        $featureFields = $values->fields($featureForm['fields'], $options['translation']?->extra_fields ?? []);

        return view('faqs::categories.edit', compact('category', 'featureForm', 'featureFields') + $options + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function update(SaveFaqCategoryRequest $request, FaqCategory $category, FaqCategoryService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->save($request->validated(), $category, $request->user('admin'));
        $language = Language::query()->findOrFail($request->validated('lang_id'));
        $notifications->sendToAdminsForActor($request->user('admin'), 'SSS kategorisi güncellendi', 'Bir SSS kategorisi güncellendi.', route('admin.faqs.categories.edit', ['category' => $category->id, 'lang' => $language->code], absolute: false));

        return redirect()->route('admin.faqs.categories.edit', ['category' => $category->id, 'lang' => $language->code])->with('notification', ['type' => 'success', 'message' => 'Kategori başarıyla güncellendi.']);
    }

    public function translationEdit(FaqCategoryTranslation $translation): RedirectResponse
    {
        $this->authorize('faqs.categories.update');

        return redirect()->route('admin.faqs.categories.edit', ['category' => $translation->faq_category_id, 'lang' => $translation->language->code]);
    }

    public function destroy(FaqCategory $category, FaqCategoryService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('faqs.categories.delete');
        $service->delete($category);
        $notifications->sendToAdminsForActor($request->user('admin'), 'SSS kategorisi silindi', 'Bir SSS kategorisi silindi.', route('admin.faqs.categories.index', absolute: false));

        return redirect()->route('admin.faqs.categories.index', array_filter(['lang' => $request->query('lang')]))->with('notification', ['type' => 'success', 'message' => 'Kategori başarıyla silindi.']);
    }

    /** @return array<string, mixed> */
    private function formOptions(FaqLanguageService $languageService, ?FaqCategory $category = null): array
    {
        $languages = $languageService->active();
        $selectedLanguage = $languageService->forAdmin(request()->query('lang'));
        $translation = $selectedLanguage ? $category?->translations()->where('lang_id', $selectedLanguage->id)->first() : null;

        return ['languages' => $languages, 'selectedLanguage' => $selectedLanguage, 'translation' => $translation, 'translatedLanguageIds' => $category?->translations()->pluck('lang_id')->map(fn ($id): int => (int) $id)->all() ?? []];
    }
}
