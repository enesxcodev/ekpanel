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
use Modules\Documents\Services\DocumentCategoryService;

class DocumentCategoryController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(): View
    {
        $this->authorize('documents.categories.view');

        return view('documents::categories.index');
    }

    public function data(DocumentCategoriesDataTable $dataTable): JsonResponse
    {
        $this->authorize('documents.categories.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('documents.categories.create');
        $featureForm = $features->form('Documents', 'category');
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('documents::categories.create', compact('featureForm', 'featureFields') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(SaveDocumentCategoryRequest $request, DocumentCategoryService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Belge kategorisi eklendi',
            'Bir belge kategorisi eklendi.',
            route('admin.documents.categories.edit', ['category' => $category->id], absolute: false),
        );

        return redirect()->route('admin.documents.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori başarıyla eklendi.']);
    }

    public function edit(DocumentCategory $category, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('documents.categories.update');
        $featureForm = $features->form('Documents', 'category');
        $featureFields = $values->fields($featureForm['fields'], $category->extra_fields ?? []);

        return view('documents::categories.edit', compact('category', 'featureForm', 'featureFields') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function update(SaveDocumentCategoryRequest $request, DocumentCategory $category, DocumentCategoryService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->save($request->validated(), $category, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Belge kategorisi güncellendi',
            'Bir belge kategorisi güncellendi.',
            route('admin.documents.categories.edit', ['category' => $category->id], absolute: false),
        );

        return redirect()->route('admin.documents.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori başarıyla güncellendi.']);
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
