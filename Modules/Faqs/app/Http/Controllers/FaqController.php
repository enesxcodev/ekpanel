<?php

namespace Modules\Faqs\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Faqs\DataTables\FaqsDataTable;
use Modules\Faqs\Http\Requests\SaveFaqRequest;
use Modules\Faqs\Models\Faq;
use Modules\Faqs\Models\FaqCategory;
use Modules\Faqs\Services\FaqService;

class FaqController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(): View
    {
        $this->authorize('faqs.view');

        return view('faqs::index');
    }

    public function data(FaqsDataTable $dataTable): JsonResponse
    {
        $this->authorize('faqs.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('faqs.create');
        $featureForm = $features->form('Faqs', 'faq');
        $featureFields = $values->fields($featureForm['fields'], []);
        $categoryOptions = $this->categoryOptions();

        return view('faqs::create', compact('featureForm', 'featureFields', 'categoryOptions') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(SaveFaqRequest $request, FaqService $service): RedirectResponse
    {
        $service->save($request->validated(), null, $request->user('admin'));

        return redirect()->route('admin.faqs.index')->with('notification', ['type' => 'success', 'message' => 'Soru başarıyla eklendi.']);
    }

    public function edit(Faq $faq, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('faqs.update');
        $featureForm = $features->form('Faqs', 'faq');
        $featureFields = $values->fields($featureForm['fields'], $faq->extra_fields ?? []);
        $categoryOptions = $this->categoryOptions();

        return view('faqs::edit', compact('faq', 'featureForm', 'featureFields', 'categoryOptions') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function update(SaveFaqRequest $request, Faq $faq, FaqService $service): RedirectResponse
    {
        $service->save($request->validated(), $faq, $request->user('admin'));

        return redirect()->route('admin.faqs.index')->with('notification', ['type' => 'success', 'message' => 'Soru başarıyla güncellendi.']);
    }

    public function destroy(Faq $faq, FaqService $service): RedirectResponse
    {
        $this->authorize('faqs.delete');
        $service->delete($faq);

        return redirect()->route('admin.faqs.index')->with('notification', ['type' => 'success', 'message' => 'Soru başarıyla silindi.']);
    }

    /** @return list<array{value: int, label: string}> */
    private function categoryOptions(): array
    {
        return FaqCategory::query()
            ->orderBy('name')
            ->get(['id', 'name'])
            ->map(fn (FaqCategory $category): array => ['value' => $category->id, 'label' => $category->name])
            ->all();
    }
}
