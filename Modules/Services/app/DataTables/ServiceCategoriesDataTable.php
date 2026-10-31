<?php

namespace Modules\Services\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Services\Models\ServiceCategory;
use Modules\Services\Services\ServicesLanguageService;
use Yajra\DataTables\Facades\DataTables;

class ServiceCategoriesDataTable
{
    public function handle(): JsonResponse
    {
        $language = app(ServicesLanguageService::class)->forAdmin(request()->query('lang'));
        $query = ServiceCategory::query()->leftJoin('service_category_translations as localized', function ($join) use ($language): void {
            $join->on('localized.service_category_id', '=', 'service_categories.id')->where('localized.lang_id', '=', $language?->id ?? 0);
        })->select(['service_categories.*', 'localized.name as name']);

        return DataTables::eloquent($query)
            ->editColumn('name', fn (ServiceCategory $category): string => $category->name ?? 'Çeviri eksik (#'.$category->id.')')
            ->filterColumn('name', fn ($query, $keyword) => $query->where('localized.name', 'like', '%'.$keyword.'%'))
            ->orderColumn('name', 'localized.name $1')
            ->editColumn('is_active', fn (ServiceCategory $category): string => $category->is_active ? 'Aktif' : 'Pasif')
            ->addColumn('actions', fn (ServiceCategory $category): string => view('services::categories.partials.actions', compact('category'))->render())
            ->removeColumn('translations')
            ->rawColumns(['actions'])
            ->toJson();
    }
}
