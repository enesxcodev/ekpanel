<?php

namespace Modules\Projects\Http\Controllers;

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
use Modules\Projects\DataTables\ProjectCategoriesDataTable;
use Modules\Projects\Http\Requests\SaveProjectCategoryRequest;
use Modules\Projects\Models\ProjectCategory;
use Modules\Projects\Services\ProjectCategoryService;
use Modules\Projects\Services\ProjectsLanguageService;

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

    public function create(ModuleFeatures $features, FeatureValues $values, MediaService $media, ProjectsLanguageService $languages, Request $request): View
    {
        $this->authorize('projects.categories.create');

        return $this->form(null, $features, $values, $media, $languages, $request);
    }

    public function store(SaveProjectCategoryRequest $request, ProjectCategoryService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Proje kategorisi eklendi',
            'Bir proje kategorisi eklendi.',
            route('admin.projects.categories.edit', ['category' => $category->id, 'lang' => 'tr'], absolute: false),
        );

        return redirect()->route('admin.projects.categories.edit', ['category' => $category->id, 'lang' => 'tr'])->with('notification', ['type' => 'success', 'message' => 'Kategori eklendi.']);
    }

    public function edit(ProjectCategory $category, ModuleFeatures $features, FeatureValues $values, MediaService $media, ProjectsLanguageService $languages, Request $request): View
    {
        $this->authorize('projects.categories.update');

        $category->load('translations');

        return $this->form($category, $features, $values, $media, $languages, $request);
    }

    public function update(SaveProjectCategoryRequest $request, ProjectCategory $category, ProjectCategoryService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->save($request->validated(), $category, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Proje kategorisi güncellendi',
            'Bir proje kategorisi güncellendi.',
            route('admin.projects.categories.edit', ['category' => $category->id, 'lang' => Language::query()->findOrFail($request->validated('lang_id'))->code], absolute: false),
        );

        $code = Language::query()->findOrFail($request->validated('lang_id'))->code;

        return redirect()->route('admin.projects.categories.edit', ['category' => $category->id, 'lang' => $code])->with('notification', ['type' => 'success', 'message' => 'Kategori güncellendi.']);
    }

    public function destroy(ProjectCategory $category, ProjectCategoryService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('projects.categories.delete');
        $service->delete($category);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Proje kategorisi silindi',
            'Bir proje kategorisi silindi.',
            route('admin.projects.categories.index', absolute: false),
        );

        return redirect()->route('admin.projects.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori silindi.']);
    }

    private function form(?ProjectCategory $category, ModuleFeatures $features, FeatureValues $values, MediaService $media, ProjectsLanguageService $languages, Request $request): View
    {
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->forAdmin($request->query('lang'));
        if ($category === null && $selectedLanguage?->code !== ProjectsLanguageService::PrimaryCode) {
            abort(404);
        }
        $translation = $category?->translations->firstWhere('lang_id', $selectedLanguage?->id);
        $translatedLanguageIds = $category?->translations->pluck('lang_id')->all() ?? [];
        $featureForm = $features->form('Projects', 'category');
        $featureFields = $values->fields($featureForm['fields'], $translation?->extra_fields ?? []);
        $mediaConfig = $media->configuration(auth('admin')->user());

        return view($category ? 'projects::categories.edit' : 'projects::categories.create', compact('category', 'featureForm', 'featureFields', 'mediaConfig', 'activeLanguages', 'selectedLanguage', 'translation', 'translatedLanguageIds'));
    }
}
