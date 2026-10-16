<?php

namespace Modules\Services\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Services\Models\Service;
use Yajra\DataTables\Facades\DataTables;

class ServicesDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(Service::query()->with('category:id,name')->select(['services.id', 'services.title', 'services.service_category_id', 'services.image', 'services.icon', 'services.is_active']))
            ->addColumn('category_name', fn (Service $service): string => $service->category?->name ?? 'Kategorisiz')
            ->addColumn('visual', fn (Service $service): string => view('services::partials.visual', compact('service'))->render())
            ->editColumn('is_active', fn (Service $service): string => $service->is_active ? 'Aktif' : 'Pasif')
            ->rawColumns(['visual'])
            ->toJson();
    }
}
