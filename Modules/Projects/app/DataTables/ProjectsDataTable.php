<?php

namespace Modules\Projects\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Projects\Models\Project;
use Yajra\DataTables\Facades\DataTables;

class ProjectsDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(Project::query()->with(['category:id,name', 'coverMedia'])->select(['projects.id', 'projects.project_category_id', 'projects.title', 'projects.project_date', 'projects.is_active', 'projects.cover_media_id', 'projects.sort_order']))
            ->addColumn('category_name', fn (Project $project): string => $project->category?->name ?? '—')
            ->addColumn('cover', fn (Project $project): string => view('projects::partials.cover', compact('project'))->render())
            ->editColumn('project_date', fn (Project $project): ?string => $project->project_date?->format('d.m.Y'))
            ->editColumn('is_active', fn (Project $project): string => $project->is_active ? 'Yayında' : 'Yayında değil')
            ->addColumn('actions', fn (Project $project): string => view('projects::partials.actions', compact('project'))->render())
            ->rawColumns(['cover', 'actions'])->toJson();
    }
}
