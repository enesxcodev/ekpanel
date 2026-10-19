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
use Modules\Documents\Services\DocumentService;

class DocumentController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(): View
    {
        $this->authorize('documents.view');

        return view('documents::index');
    }

    public function data(DocumentsDataTable $dataTable): JsonResponse
    {
        $this->authorize('documents.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('documents.create');
        $featureForm = $features->form('Documents', 'document');
        $featureFields = $values->fields($featureForm['fields'], []);
        $categoryOptions = $this->categoryOptions();

        return view('documents::create', compact('featureForm', 'featureFields', 'categoryOptions') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(SaveDocumentRequest $request, DocumentService $service, NotifyService $notifications): RedirectResponse
    {
        $document = $service->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Belge eklendi',
            'Bir belge eklendi.',
            route('admin.documents.edit', ['document' => $document->id], absolute: false),
        );

        return redirect()->route('admin.documents.index')->with('notification', ['type' => 'success', 'message' => 'Belge başarıyla eklendi.']);
    }

    public function edit(Document $document, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('documents.update');
        $featureForm = $features->form('Documents', 'document');
        $document->load('fileMedia');
        $featureFields = $values->fields($featureForm['fields'], $document->extra_fields ?? []);
        $categoryOptions = $this->categoryOptions();

        return view('documents::edit', compact('document', 'featureForm', 'featureFields', 'categoryOptions') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function update(SaveDocumentRequest $request, Document $document, DocumentService $service, NotifyService $notifications): RedirectResponse
    {
        $document = $service->save($request->validated(), $document, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Belge güncellendi',
            'Bir belge güncellendi.',
            route('admin.documents.edit', ['document' => $document->id], absolute: false),
        );

        return redirect()->route('admin.documents.index')->with('notification', ['type' => 'success', 'message' => 'Belge başarıyla güncellendi.']);
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
    private function categoryOptions(): array
    {
        return DocumentCategory::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (DocumentCategory $category): array => ['value' => $category->id, 'label' => $category->name])
            ->all();
    }
}
