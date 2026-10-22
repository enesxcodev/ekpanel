<?php

namespace Modules\Branches\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Branches\Models\BranchCategory;
use Yajra\DataTables\Facades\DataTables;

class BranchCategoriesDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(BranchCategory::query()->select('branch_categories.*')->withCount('branches'))
            ->editColumn('is_active', fn (BranchCategory $category): string => $category->is_active ? 'Aktif' : 'Pasif')
            ->addColumn('actions', fn (BranchCategory $category): string => view('branches::categories.partials.actions', compact('category'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
