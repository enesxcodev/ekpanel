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
use Modules\Documents\DataTables\DocumentsDataTable;
use Modules\Documents\Http\Requests\SaveDocumentRequest;
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentCategory;
use Modules\Documents\Models\DocumentTranslation;
use Modules\Documents\Services\DocumentLanguageService;
use Modules\Documents\Services\DocumentService;

class DocumentController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(DocumentLanguageService $languages, Request $request): View
    {
        $this->authorize('documents.view');

        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);

        return view('documents::index', compact('activeLanguages', 'selectedLanguage'));
    }

    public function data(DocumentsDataTable $dataTable): JsonResponse
    {
        $this->authorize('documents.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values, DocumentLanguageService $languages, Request $request): View
    {
        $this->authorize('documents.create');
        $featureForm = $features->form('Documents', 'document');
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);
        $featureFields = $values->fields($featureForm['fields'], []);
        $categoryOptions = $this->categoryOptions($selectedLanguage?->id);

        return view('documents::create', compact('featureForm', 'featureFields', 'categoryOptions', 'activeLanguages', 'selectedLanguage') + ['translatedLanguageIds' => [], 'translation' => null, 'mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(SaveDocumentRequest $request, DocumentService $service, NotifyService $notifications): RedirectResponse
    {
        $document = $service->save($request->validated(), null, $request->user('admin'));
        $langCode = $document->translations->firstWhere('lang_id', $request->validated('lang_id'))?->language?->code;
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Belge eklendi',
            'Bir belge eklendi.',
            route('admin.documents.edit', ['document' => $document->id, 'lang' => $langCode], absolute: false),
        );

        return redirect()->route('admin.documents.edit', ['document' => $document->id, 'lang' => $langCode])->with('notification', ['type' => 'success', 'message' => 'Belge başarıyla eklendi.']);
    }

    public function edit(Document $document, ModuleFeatures $features, FeatureValues $values, DocumentLanguageService $languages, Request $request): View
    {
        $this->authorize('documents.update');
        $featureForm = $features->form('Documents', 'document');
        $document->load('fileMedia');
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);
        $translation = $selectedLanguage ? $document->translations()->where('lang_id', $selectedLanguage->id)->first() : null;
        $translatedLanguageIds = $languages->translatedLanguageIds($document);
        $featureFields = $values->fields($featureForm['fields'], $translation?->extra_fields ?? []);
        $categoryOptions = $this->categoryOptions($selectedLanguage?->id);

        return view('documents::edit', compact('document', 'featureForm', 'featureFields', 'categoryOptions', 'activeLanguages', 'selectedLanguage', 'translation', 'translatedLanguageIds') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function editTranslation(DocumentTranslation $translation): RedirectResponse
    {
        $this->authorize('documents.update');
        $translation->load('language');

        return redirect()->route('admin.documents.edit', ['document' => $translation->document_id, 'lang' => $translation->language?->code]);
    }

    public function update(SaveDocumentRequest $request, Document $document, DocumentService $service, NotifyService $notifications): RedirectResponse
    {
        $document = $service->save($request->validated(), $document, $request->user('admin'));
        $langCode = $document->translations->firstWhere('lang_id', $request->validated('lang_id'))?->language?->code;
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Belge güncellendi',
            'Bir belge güncellendi.',
            route('admin.documents.edit', ['document' => $document->id, 'lang' => $langCode], absolute: false),
        );

        return redirect()->route('admin.documents.edit', ['document' => $document->id, 'lang' => $langCode])->with('notification', ['type' => 'success', 'message' => 'Belge başarıyla güncellendi.']);
    }

    public function destroy(Document $document, DocumentService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('documents.delete');
        $service->delete($document);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Belge silindi',
            'Bir belge silindi.',
            route('admin.documents.index', absolute: false),
        );

        return redirect()->route('admin.documents.index')->with('notification', ['type' => 'success', 'message' => 'Belge başarıyla silindi.']);
    }

    /** @return list<array{value: int, label: string}> */
    private function categoryOptions(?int $languageId): array
    {
        if ($languageId === null) {
            return [];
        }

        return DocumentCategory::query()
            ->whereHas('translations', fn ($query) => $query->where('lang_id', $languageId))
            ->with(['translations' => fn ($query) => $query->where('lang_id', $languageId)])
            ->orderBy('id')
            ->get(['id'])
            ->sortBy(fn (DocumentCategory $category): string => $category->translations->first()?->name ?? '')
            ->map(fn (DocumentCategory $category): array => ['value' => $category->id, 'label' => $category->translations->first()?->name ?? ''])
            ->all();
    }
}
