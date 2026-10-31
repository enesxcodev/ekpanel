<?php

namespace Modules\Services\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Services\Models\Service;
use Modules\Services\Services\ServicesLanguageService;
use Yajra\DataTables\Facades\DataTables;

class ServicesDataTable
{
    public function handle(): JsonResponse
    {
        $language = app(ServicesLanguageService::class)->forAdmin(request()->query('lang'));
        $query = Service::query()->with('category')->leftJoin('service_translations as localized', function ($join) use ($language): void {
            $join->on('localized.service_id', '=', 'services.id')->where('localized.lang_id', '=', $language?->id ?? 0);
        })->select(['services.*', 'localized.title as title', 'localized.slug as slug']);

        return DataTables::eloquent($query)
            ->editColumn('title', fn (Service $service): string => $service->title ?? 'Çeviri eksik (#'.$service->id.')')
            ->filterColumn('title', fn ($query, $keyword) => $query->where('localized.title', 'like', '%'.$keyword.'%'))
            ->orderColumn('title', 'localized.title $1')
            ->filterColumn('slug', fn ($query, $keyword) => $query->where('localized.slug', 'like', '%'.$keyword.'%'))
            ->orderColumn('slug', 'localized.slug $1')
            ->addColumn('category_name', fn (Service $service): string => $service->category?->name ?? 'Kategorisiz')
            ->filterColumn('category_name', fn ($query, $keyword) => $query->whereHas('category.translations', fn ($translation) => $translation->where('lang_id', $language?->id ?? 0)->where('name', 'like', '%'.$keyword.'%')))
            ->addColumn('visual', fn (Service $service): string => view('services::partials.visual', compact('service'))->render())
            ->editColumn('is_active', fn (Service $service): string => $service->is_active ? 'Aktif' : 'Pasif')
            ->addColumn('actions', fn (Service $service): string => view('services::partials.actions', compact('service'))->render())
            ->removeColumn('translations')
            ->rawColumns(['visual', 'actions'])
            ->toJson();
    }
}
