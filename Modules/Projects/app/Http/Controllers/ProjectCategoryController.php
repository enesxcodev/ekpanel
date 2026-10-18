<?php

namespace Modules\Projects\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Projects\DataTables\ProjectCategoriesDataTable;
use Modules\Projects\Http\Requests\SaveProjectCategoryRequest;
use Modules\Projects\Models\ProjectCategory;
use Modules\Projects\Services\ProjectCategoryService;

class ProjectCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('projects.categories.view');

        return view('projects::categories.index');
    }

    public function data(ProjectCategoriesDataTable $dataTable): JsonResponse
    {
        $this->authorize('projects.categories.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values, MediaService $media): View
    {
        $this->authorize('projects.categories.create');

        return $this->form(null, $features, $values, $media);
    }

    public function store(SaveProjectCategoryRequest $request, ProjectCategoryService $service): RedirectResponse
    {
        $service->save($request->validated(), null, $request->user('admin'));

        return redirect()->route('admin.projects.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori eklendi.']);
    }

    public function edit(ProjectCategory $category, ModuleFeatures $features, FeatureValues $values, MediaService $media): View
    {
        $this->authorize('projects.categories.update');

        return $this->form($category, $features, $values, $media);
    }

    public function update(SaveProjectCategoryRequest $request, ProjectCategory $category, ProjectCategoryService $service): RedirectResponse
    {
        $service->save($request->validated(), $category, $request->user('admin'));

        return redirect()->route('admin.projects.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori güncellendi.']);
    }

    public function destroy(ProjectCategory $category, ProjectCategoryService $service): RedirectResponse
    {
        $this->authorize('projects.categories.delete');
        $service->delete($category);

        return redirect()->route('admin.projects.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori silindi.']);
    }

    private function form(?ProjectCategory $category, ModuleFeatures $features, FeatureValues $values, MediaService $media): View
    {
        $featureForm = $features->form('Projects', 'category');
        $featureFields = $values->fields($featureForm['fields'], $category?->extra_fields ?? []);
        $categoryOptions = ProjectCategory::query()->orderBy('name')->get()->map(fn (ProjectCategory $entry): array => ['value' => $entry->id, 'label' => $entry->name])->all();
        $mediaConfig = $media->configuration(auth('admin')->user());

        return view($category ? 'projects::categories.edit' : 'projects::categories.create', compact('category', 'featureForm', 'featureFields', 'categoryOptions', 'mediaConfig'));
    }
}
