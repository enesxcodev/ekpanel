<?php

namespace Modules\Projects\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Projects\Models\ProjectCategory;
use Modules\Projects\Services\ProjectsLanguageService;
use Yajra\DataTables\Facades\DataTables;

class ProjectCategoriesDataTable
{
    public function handle(): JsonResponse
    {
        $language = app(ProjectsLanguageService::class)->forAdmin(request()->query('lang'));
        $languageId = $language?->id ?? 0;

        return DataTables::eloquent(ProjectCategory::query()->join('project_category_translations as category_translation', function ($join) use ($languageId): void {
            $join->on('category_translation.project_category_id', '=', 'project_categories.id')->where('category_translation.lang_id', '=', $languageId);
        })->select(['project_categories.id', 'category_translation.name as name', 'category_translation.slug as slug', 'project_categories.icon']))
            ->addColumn('actions', fn (ProjectCategory $category): string => view('projects::categories.partials.actions', compact('category'))->render())
            ->rawColumns(['actions'])->toJson();
    }
}
