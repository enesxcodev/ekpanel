<?php

namespace Modules\Branches\DataTables;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Branches\Models\Branch;
use Yajra\DataTables\Facades\DataTables;

class BranchesDataTable
{
    public function __construct(private Request $request) {}

    public function handle(): JsonResponse
    {
        $query = Branch::query()->with(['category:id,name', 'city:id,name', 'town:id,name'])
            ->select(['id', 'branch_category_id', 'name', 'city_id', 'town_id', 'phone', 'is_active', 'sort_order']);
        $categoryId = $this->request->integer('category_id');
        if ($categoryId > 0) {
            $query->where('branch_category_id', $categoryId);
        }
        $active = $this->request->input('is_active');
        if (in_array($active, ['0', '1', 0, 1], true)) {
            $query->where('is_active', (bool) $active);
        }

        return DataTables::eloquent($query)
            ->addColumn('category_name', fn (Branch $branch): string => $branch->category?->name ?? '')
            ->addColumn('location_name', fn (Branch $branch): string => collect([$branch->city?->name, $branch->town?->name])->filter()->implode(' / '))
            ->editColumn('is_active', fn (Branch $branch): string => $branch->is_active ? 'Aktif' : 'Pasif')
            ->addColumn('actions', fn (Branch $branch): string => view('branches::partials.actions', compact('branch'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
