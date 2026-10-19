<?php

namespace Modules\Faqs\Http\Controllers;

use App\Http\Controllers\Controller;
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
use Modules\Faqs\Services\FaqCategoryService;

class FaqCategoryController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(): View
    {
        $this->authorize('faqs.categories.view');

        return view('faqs::categories.index');
    }

    public function data(FaqCategoriesDataTable $dataTable): JsonResponse
    {
        $this->authorize('faqs.categories.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('faqs.categories.create');
        $featureForm = $features->form('Faqs', 'category');
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('faqs::categories.create', compact('featureForm', 'featureFields') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(SaveFaqCategoryRequest $request, FaqCategoryService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'SSS kategorisi eklendi',
            'Bir sss kategorisi eklendi.',
            route('admin.faqs.categories.edit', ['category' => $category->id], absolute: false),
        );

        return redirect()->route('admin.faqs.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori başarıyla eklendi.']);
    }

    public function edit(FaqCategory $category, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('faqs.categories.update');
        $featureForm = $features->form('Faqs', 'category');
        $featureFields = $values->fields($featureForm['fields'], $category->extra_fields ?? []);

        return view('faqs::categories.edit', compact('category', 'featureForm', 'featureFields') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function update(SaveFaqCategoryRequest $request, FaqCategory $category, FaqCategoryService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->save($request->validated(), $category, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'SSS kategorisi güncellendi',
            'Bir sss kategorisi güncellendi.',
            route('admin.faqs.categories.edit', ['category' => $category->id], absolute: false),
        );

        return redirect()->route('admin.faqs.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori başarıyla güncellendi.']);
    }

    public function destroy(FaqCategory $category, FaqCategoryService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('faqs.categories.delete');
        $service->delete($category);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'SSS kategorisi silindi',
            'Bir sss kategorisi silindi.',
            route('admin.faqs.categories.index', absolute: false),
        );

        return redirect()->route('admin.faqs.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori başarıyla silindi.']);
    }
}
