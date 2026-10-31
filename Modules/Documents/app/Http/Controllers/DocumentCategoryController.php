<?php

namespace Modules\Documents\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Documents\DataTables\DocumentCategoriesDataTable;
use Modules\Documents\Http\Requests\SaveDocumentCategoryRequest;
use Modules\Documents\Models\DocumentCategory;
use Modules\Documents\Models\DocumentCategoryTranslation;
use Modules\Documents\Services\DocumentCategoryService;
use Modules\Documents\Services\DocumentLanguageService;

class DocumentCategoryController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(DocumentLanguageService $languages, Request $request): View
    {
        $this->authorize('documents.categories.view');

        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);

        return view('documents::categories.index', compact('activeLanguages', 'selectedLanguage'));
    }

    public function data(DocumentCategoriesDataTable $dataTable): JsonResponse
    {
        $this->authorize('documents.categories.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values, DocumentLanguageService $languages, Request $request): View
    {
        $this->authorize('documents.categories.create');
        $featureForm = $features->form('Documents', 'category');
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('documents::categories.create', compact('featureForm', 'featureFields', 'activeLanguages', 'selectedLanguage') + ['translatedLanguageIds' => [], 'translation' => null, 'mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(SaveDocumentCategoryRequest $request, DocumentCategoryService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->save($request->validated(), null, $request->user('admin'));
        $langCode = $category->translations->firstWhere('lang_id', $request->validated('lang_id'))?->language?->code;
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Belge kategorisi eklendi',
            'Bir belge kategorisi eklendi.',
            route('admin.documents.categories.edit', ['category' => $category->id, 'lang' => $langCode], absolute: false),
        );

        return redirect()->route('admin.documents.categories.edit', ['category' => $category->id, 'lang' => $langCode])->with('notification', ['type' => 'success', 'message' => 'Kategori başarıyla eklendi.']);
    }

    public function edit(DocumentCategory $category, ModuleFeatures $features, FeatureValues $values, DocumentLanguageService $languages, Request $request): View
    {
        $this->authorize('documents.categories.update');
        $featureForm = $features->form('Documents', 'category');
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);
        $translation = $selectedLanguage ? $category->translations()->where('lang_id', $selectedLanguage->id)->first() : null;
        $translatedLanguageIds = $languages->translatedLanguageIds($category);
        $featureFields = $values->fields($featureForm['fields'], $translation?->extra_fields ?? []);

        return view('documents::categories.edit', compact('category', 'featureForm', 'featureFields', 'activeLanguages', 'selectedLanguage', 'translation', 'translatedLanguageIds') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function editTranslation(DocumentCategoryTranslation $translation): RedirectResponse
    {
        $this->authorize('documents.categories.update');
        $translation->load('language');

        return redirect()->route('admin.documents.categories.edit', ['category' => $translation->document_category_id, 'lang' => $translation->language?->code]);
    }

    public function update(SaveDocumentCategoryRequest $request, DocumentCategory $category, DocumentCategoryService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->save($request->validated(), $category, $request->user('admin'));
        $langCode = $category->translations->firstWhere('lang_id', $request->validated('lang_id'))?->language?->code;
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Belge kategorisi güncellendi',
            'Bir belge kategorisi güncellendi.',
            route('admin.documents.categories.edit', ['category' => $category->id, 'lang' => $langCode], absolute: false),
        );

        return redirect()->route('admin.documents.categories.edit', ['category' => $category->id, 'lang' => $langCode])->with('notification', ['type' => 'success', 'message' => 'Kategori başarıyla güncellendi.']);
    }

    public function destroy(DocumentCategory $category, DocumentCategoryService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('documents.categories.delete');
        $service->delete($category);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Belge kategorisi silindi',
            'Bir belge kategorisi silindi.',
            route('admin.documents.categories.index', absolute: false),
        );

        return redirect()->route('admin.documents.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori başarıyla silindi.']);
    }
}
