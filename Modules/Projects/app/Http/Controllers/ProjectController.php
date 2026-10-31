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
use Modules\Projects\DataTables\ProjectsDataTable;
use Modules\Projects\Http\Requests\SaveProjectRequest;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectCategory;
use Modules\Projects\Services\ProjectService;
use Modules\Projects\Services\ProjectsLanguageService;

class ProjectController extends Controller
{
    public function index(): View
    {
        $this->authorize('projects.view');

        return view('projects::index');
    }

    public function data(ProjectsDataTable $dataTable): JsonResponse
    {
        $this->authorize('projects.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values, MediaService $media, ProjectsLanguageService $languages, Request $request): View
    {
        $this->authorize('projects.create');

        return $this->form(null, $features, $values, $media, $languages, $request);
    }

    public function store(SaveProjectRequest $request, ProjectService $service, NotifyService $notifications): RedirectResponse
    {
        $project = $service->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Proje eklendi',
            'Bir proje eklendi.',
            route('admin.projects.edit', ['project' => $project->id, 'lang' => 'tr'], absolute: false),
        );

        return redirect()->route('admin.projects.edit', ['project' => $project->id, 'lang' => 'tr'])->with('notification', ['type' => 'success', 'message' => 'Proje eklendi.']);
    }

    public function edit(Project $project, ModuleFeatures $features, FeatureValues $values, MediaService $media, ProjectsLanguageService $languages, Request $request): View
    {
        $this->authorize('projects.update');
        $project->load(['images', 'coverMedia', 'translations']);

        return $this->form($project, $features, $values, $media, $languages, $request);
    }

    public function update(SaveProjectRequest $request, Project $project, ProjectService $service, NotifyService $notifications): RedirectResponse
    {
        $project = $service->save($request->validated(), $project, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Proje güncellendi',
            'Bir proje güncellendi.',
            route('admin.projects.edit', ['project' => $project->id, 'lang' => Language::query()->findOrFail($request->validated('lang_id'))->code], absolute: false),
        );

        $code = Language::query()->findOrFail($request->validated('lang_id'))->code;

        return redirect()->route('admin.projects.edit', ['project' => $project->id, 'lang' => $code])->with('notification', ['type' => 'success', 'message' => 'Proje güncellendi.']);
    }

    public function destroy(Project $project, ProjectService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('projects.delete');
        $service->delete($project);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Proje silindi',
            'Bir proje silindi.',
            route('admin.projects.index', absolute: false),
        );

        return redirect()->route('admin.projects.index')->with('notification', ['type' => 'success', 'message' => 'Proje silindi.']);
    }

    private function form(?Project $project, ModuleFeatures $features, FeatureValues $values, MediaService $media, ProjectsLanguageService $languages, Request $request): View
    {
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->forAdmin($request->query('lang'));
        if ($project === null && $selectedLanguage?->code !== ProjectsLanguageService::PrimaryCode) {
            abort(404);
        }
        $translation = $project?->translations->firstWhere('lang_id', $selectedLanguage?->id);
        $translatedLanguageIds = $project?->translations->pluck('lang_id')->all() ?? [];
        $featureForm = $features->form('Projects', 'project');
        $featureFields = $values->fields($featureForm['fields'], $translation?->extra_fields ?? []);
        $categoryOptions = ProjectCategory::query()->whereHas('translations', fn ($query) => $query->where('lang_id', $selectedLanguage?->id))->with(['translations' => fn ($query) => $query->where('lang_id', $selectedLanguage?->id)])->get()->map(fn (ProjectCategory $entry): array => ['value' => $entry->id, 'label' => $entry->translations->first()?->name ?? ''])->all();
        $mediaConfig = $media->configuration(auth('admin')->user());

        return view($project ? 'projects::edit' : 'projects::create', compact('project', 'featureForm', 'featureFields', 'categoryOptions', 'mediaConfig', 'activeLanguages', 'selectedLanguage', 'translation', 'translatedLanguageIds'));
    }
}
