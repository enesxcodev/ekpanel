<?php

namespace Modules\Branches\DataTables;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Branches\Models\Branch;
use Modules\Branches\Services\BranchesLanguageService;
use Yajra\DataTables\Facades\DataTables;

class BranchesDataTable
{
    public function __construct(private Request $request) {}

    public function handle(): JsonResponse
    {
        $language = app(BranchesLanguageService::class)->forAdmin($this->request->query('lang'));
        $query = Branch::query()->with(['city:id,name', 'town:id,name'])
            ->leftJoin('branch_translations as localized', function ($join) use ($language): void {
                $join->on('localized.branch_id', '=', 'branches.id')->where('localized.lang_id', '=', $language?->id ?? 0);
            })->leftJoin('branch_category_translations as category_localized', function ($join) use ($language): void {
                $join->on('category_localized.branch_category_id', '=', 'branches.branch_category_id')->where('category_localized.lang_id', '=', $language?->id ?? 0);
            })->select(['branches.id', 'branches.branch_category_id', 'branches.city_id', 'branches.town_id', 'branches.is_active', 'branches.sort_order', 'localized.name', 'localized.phone', 'category_localized.name as category_name']);
        $categoryId = $this->request->integer('category_id');
        if ($categoryId > 0) {
            $query->where('branches.branch_category_id', $categoryId);
        }
        $active = $this->request->input('is_active');
        if (in_array($active, ['0', '1', 0, 1], true)) {
            $query->where('is_active', (bool) $active);
        }

        return DataTables::eloquent($query)
            ->editColumn('name', fn (Branch $branch): string => $branch->name ?? 'Çeviri eksik (#'.$branch->id.')')
            ->addColumn('category_name', fn (Branch $branch): string => $branch->category_name ?? 'Çeviri eksik')
            ->addColumn('location_name', fn (Branch $branch): string => collect([$branch->city?->name, $branch->town?->name])->filter()->implode(' / '))
            ->editColumn('is_active', fn (Branch $branch): string => $branch->is_active ? 'Aktif' : 'Pasif')
            ->addColumn('actions', fn (Branch $branch): string => view('branches::partials.actions', compact('branch'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
