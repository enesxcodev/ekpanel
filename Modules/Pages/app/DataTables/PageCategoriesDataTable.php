<?php

namespace Modules\Pages\DataTables;

use App\Models\Settings\Language;
use Illuminate\Http\JsonResponse;
use Modules\Pages\Models\PageCategory;
use Modules\Pages\Services\PagesLanguageService;
use Yajra\DataTables\Facades\DataTables;

class PageCategoriesDataTable
{
    public function handle(): JsonResponse
    {
        $code = request()->query('lang', PagesLanguageService::PrimaryCode);
        $language = Language::query()->where('code', $code)->where('is_active', true)->first() ?? Language::query()->where('code', PagesLanguageService::PrimaryCode)->where('is_active', true)->first();

        return DataTables::eloquent(PageCategory::query()->with(['parent.translations' => fn ($query) => $query->where('lang_id', $language?->id)])
            ->leftJoin('page_category_translations as localized', function ($join) use ($language): void {
                $join->on('localized.page_category_id', '=', 'page_categories.id')->where('localized.lang_id', $language?->id);
            })
            ->select(['page_categories.id', 'page_categories.parent_id', 'page_categories.sort_order', 'localized.name', 'localized.slug']))
            ->addColumn('parent_name', fn (PageCategory $category): string => $category->parent?->translations->first()?->name ?? '—')
            ->addColumn('actions', fn (PageCategory $category): string => view('pages::categories.partials.actions', compact('category'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
