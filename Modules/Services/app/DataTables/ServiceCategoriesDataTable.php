<?php

namespace Modules\Services\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Services\Models\ServiceCategory;
use Yajra\DataTables\Facades\DataTables;

class ServiceCategoriesDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(ServiceCategory::query()->select(['id', 'name']))
            ->addColumn('actions', fn (ServiceCategory $category): string => view('services::categories.partials.actions', compact('category'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
