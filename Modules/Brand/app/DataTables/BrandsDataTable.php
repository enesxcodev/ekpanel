<?php

namespace Modules\Brand\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Brand\Models\Brand;
use Yajra\DataTables\Facades\DataTables;

class BrandsDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(Brand::query()->select(['id', 'name', 'image', 'short_description', 'link', 'created_at']))
            ->addColumn('visual', fn (Brand $brand): string => view('brand::partials.image', compact('brand'))->render())
            ->editColumn('created_at', fn (Brand $brand): ?string => $brand->created_at?->format('d.m.Y H:i'))
            ->addColumn('actions', fn (Brand $brand): string => view('brand::partials.actions', compact('brand'))->render())
            ->rawColumns(['visual', 'actions'])
            ->toJson();
    }
}
