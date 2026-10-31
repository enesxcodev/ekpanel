<?php

namespace Modules\Projects\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Projects\Models\Project;
use Modules\Projects\Services\ProjectsLanguageService;
use Yajra\DataTables\Facades\DataTables;

class ProjectsDataTable
{
    public function handle(): JsonResponse
    {
        $language = app(ProjectsLanguageService::class)->forAdmin(request()->query('lang'));
        $languageId = $language?->id ?? 0;

        return DataTables::eloquent(Project::query()->with(['category.selectedTranslation', 'coverMedia'])->join('project_translations as project_translation', function ($join) use ($languageId): void {
            $join->on('project_translation.project_id', '=', 'projects.id')->where('project_translation.lang_id', '=', $languageId);
        })->select(['projects.id', 'projects.project_category_id', 'project_translation.title as title', 'project_date', 'projects.is_active', 'projects.cover_media_id', 'projects.sort_order']))
            ->addColumn('category_name', fn (Project $project): string => $project->category?->selectedTranslation?->name ?? '—')
            ->addColumn('cover', fn (Project $project): string => view('projects::partials.cover', compact('project'))->render())
            ->editColumn('project_date', fn (Project $project): ?string => $project->project_date?->format('d.m.Y'))
            ->editColumn('is_active', fn (Project $project): string => $project->is_active ? 'Yayında' : 'Yayında değil')
            ->addColumn('actions', fn (Project $project): string => view('projects::partials.actions', compact('project'))->render())
            ->rawColumns(['cover', 'actions'])->toJson();
    }
}
