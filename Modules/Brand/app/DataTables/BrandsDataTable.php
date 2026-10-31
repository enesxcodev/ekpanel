<?php

namespace Modules\Brand\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Brand\Models\Brand;
use Modules\Brand\Services\BrandLanguageService;
use Yajra\DataTables\Facades\DataTables;

class BrandsDataTable
{
    public function handle(): JsonResponse
    {
        $languageId = app(BrandLanguageService::class)->active()->firstWhere('code', 'tr')?->id ?? 0;

        return DataTables::eloquent(Brand::query()->leftJoin('brand_translations as tr_translation', function ($join) use ($languageId): void {
            $join->on('tr_translation.brand_id', '=', 'brands.id')->where('tr_translation.lang_id', '=', $languageId);
        })->select(['brands.id', 'tr_translation.name', 'tr_translation.short_description', 'brands.image', 'brands.image_media_id', 'brands.link', 'brands.sort_order', 'brands.is_active', 'brands.created_at']))
            ->addColumn('visual', fn (Brand $brand): string => view('brand::partials.image', compact('brand'))->render())
            ->editColumn('is_active', fn (Brand $brand): string => $brand->is_active ? 'Aktif' : 'Pasif')
            ->editColumn('created_at', fn (Brand $brand): ?string => $brand->created_at?->format('d.m.Y H:i'))
            ->addColumn('actions', fn (Brand $brand): string => view('brand::partials.actions', compact('brand'))->render())
            ->rawColumns(['visual', 'actions'])
            ->toJson();
    }
}
