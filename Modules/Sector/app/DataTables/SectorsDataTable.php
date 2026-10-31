<?php

namespace Modules\Sector\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Sector\Models\Sector;
use Modules\Sector\Services\SectorLanguageService;
use Yajra\DataTables\Facades\DataTables;

class SectorsDataTable
{
    public function handle(SectorLanguageService $languages): JsonResponse
    {
        $language = $languages->forAdmin(request()->query('lang'));
        $query = Sector::query()->select(['sectors.id', 'sectors.sort_order', 'sectors.image', 'sectors.image_media_id', 'sectors.is_active', 'sectors.created_at'])
            ->join('sector_translations', function ($join) use ($language): void {
                $join->on('sectors.id', '=', 'sector_translations.sector_id')->where('sector_translations.lang_id', $language?->id ?? 0);
            })->addSelect(['sector_translations.title', 'sector_translations.slug']);

        return DataTables::eloquent($query)
            ->addColumn('photo', fn (Sector $sector): string => view('sector::partials.photo', compact('sector'))->render())
            ->editColumn('is_active', fn (Sector $sector): string => view('sector::partials.status', compact('sector'))->render())
            ->editColumn('created_at', fn (Sector $sector): ?string => $sector->created_at?->format('d.m.Y H:i'))
            ->addColumn('actions', fn (Sector $sector): string => view('sector::partials.actions', compact('sector'))->render())
            ->rawColumns(['photo', 'is_active', 'actions'])
            ->toJson();
    }
}
