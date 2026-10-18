<?php

namespace Modules\Projects\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Projects\Models\ProjectCategory;
use Yajra\DataTables\Facades\DataTables;

class ProjectCategoriesDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(ProjectCategory::query()->select(['id', 'name', 'slug', 'icon']))
            ->addColumn('actions', fn (ProjectCategory $category): string => view('projects::categories.partials.actions', compact('category'))->render())
            ->rawColumns(['actions'])->toJson();
    }
}
