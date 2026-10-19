<?php

namespace Modules\Galeriler\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Galeriler\Http\Requests\SaveGalerilerCategoryRequest;
use Modules\Galeriler\Models\GalerilerCategory;
use Modules\Galeriler\Services\GalerilerCategoryService;

class GalerilerCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('galeriler.categories.view');

        return view('galeriler::categories.index', ['categories' => GalerilerCategory::query()->orderBy('name')->orderBy('id')->get()]);
    }

    public function create(ModuleFeatures $features, FeatureValues $values, MediaService $media): View
    {
        $this->authorize('galeriler.categories.create');

        return $this->form(null, $features, $values, $media);
    }

    public function store(SaveGalerilerCategoryRequest $request, GalerilerCategoryService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Galeri kategorisi eklendi',
            'Bir galeri kategorisi eklendi.',
            route('admin.galeriler.categories.edit', ['category' => $category->id], absolute: false),
        );

        return redirect()->route('admin.galeriler.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori eklendi.']);
    }

    public function edit(GalerilerCategory $category, ModuleFeatures $features, FeatureValues $values, MediaService $media): View
    {
        $this->authorize('galeriler.categories.update');

        return $this->form($category, $features, $values, $media);
    }

    public function update(SaveGalerilerCategoryRequest $request, GalerilerCategory $category, GalerilerCategoryService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->save($request->validated(), $category, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Galeri kategorisi güncellendi',
            'Bir galeri kategorisi güncellendi.',
            route('admin.galeriler.categories.edit', ['category' => $category->id], absolute: false),
        );

        return redirect()->route('admin.galeriler.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori güncellendi.']);
    }

    public function destroy(GalerilerCategory $category, GalerilerCategoryService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('galeriler.categories.delete');
        $service->delete($category);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Galeri kategorisi silindi',
            'Bir galeri kategorisi silindi.',
            route('admin.galeriler.categories.index', absolute: false),
        );

        return redirect()->route('admin.galeriler.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori silindi.']);
    }

    private function form(?GalerilerCategory $category, ModuleFeatures $features, FeatureValues $values, MediaService $media): View
    {
        $featureForm = $features->form('Galeriler', 'category');
        $featureFields = $values->fields($featureForm['fields'], $category?->extra_fields ?? []);
        $categoryOptions = GalerilerCategory::query()->whereNotIn('id', $category ? [$category->id, ...$category->descendantIds()] : [])->orderBy('name')->get()->map(fn (GalerilerCategory $entry): array => ['value' => $entry->id, 'label' => $entry->name])->all();
        $mediaConfig = $media->configuration(auth('admin')->user());

        return view($category ? 'galeriler::categories.edit' : 'galeriler::categories.create', compact('category', 'featureForm', 'featureFields', 'categoryOptions', 'mediaConfig'));
    }
}
