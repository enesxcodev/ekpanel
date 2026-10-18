<?php

namespace Modules\Sector\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Sector\Models\Sector;
use Yajra\DataTables\Facades\DataTables;

class SectorsDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(Sector::query()->select(['id', 'title', 'slug', 'sort_order', 'image', 'is_active', 'created_at']))
            ->addColumn('photo', fn (Sector $sector): string => view('sector::partials.photo', compact('sector'))->render())
            ->editColumn('is_active', fn (Sector $sector): string => view('sector::partials.status', compact('sector'))->render())
            ->editColumn('created_at', fn (Sector $sector): ?string => $sector->created_at?->format('d.m.Y H:i'))
            ->addColumn('actions', fn (Sector $sector): string => view('sector::partials.actions', compact('sector'))->render())
            ->rawColumns(['photo', 'is_active', 'actions'])
            ->toJson();
    }
}
