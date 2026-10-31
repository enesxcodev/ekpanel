<?php

namespace Modules\Branches\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Branches\Models\BranchCategory;
use Modules\Branches\Services\BranchesLanguageService;
use Yajra\DataTables\Facades\DataTables;

class BranchCategoriesDataTable
{
    public function handle(): JsonResponse
    {
        $language = app(BranchesLanguageService::class)->forAdmin(request()->query('lang'));
        $query = BranchCategory::query()->leftJoin('branch_category_translations as localized', function ($join) use ($language): void {
            $join->on('localized.branch_category_id', '=', 'branch_categories.id')->where('localized.lang_id', '=', $language?->id ?? 0);
        })->select(['branch_categories.id', 'branch_categories.slug', 'branch_categories.sort_order', 'branch_categories.is_active', 'localized.name'])->withCount('branches');

        return DataTables::eloquent($query)
            ->editColumn('name', fn (BranchCategory $category): string => $category->name ?? 'Çeviri eksik (#'.$category->id.')')
            ->editColumn('is_active', fn (BranchCategory $category): string => $category->is_active ? 'Aktif' : 'Pasif')
            ->addColumn('actions', fn (BranchCategory $category): string => view('branches::categories.partials.actions', compact('category'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
