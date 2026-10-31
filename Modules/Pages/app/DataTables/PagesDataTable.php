<?php

namespace Modules\Pages\DataTables;

use App\Models\Settings\Language;
use Illuminate\Http\JsonResponse;
use Modules\Pages\Models\Page;
use Modules\Pages\Services\PagesLanguageService;
use Yajra\DataTables\Facades\DataTables;

class PagesDataTable
{
    public function handle(): JsonResponse
    {
        $code = request()->query('lang', PagesLanguageService::PrimaryCode);
        $language = Language::query()->where('code', $code)->where('is_active', true)->first() ?? Language::query()->where('code', PagesLanguageService::PrimaryCode)->where('is_active', true)->first();
        $query = Page::query()->with(['category.translations' => fn ($query) => $query->where('lang_id', $language?->id)])
            ->leftJoin('page_translations as localized', function ($join) use ($language): void {
                $join->on('localized.page_id', '=', 'pages.id')->where('localized.lang_id', $language?->id);
            })
            ->select(['pages.id', 'pages.page_category_id', 'pages.status', 'pages.sort_order', 'localized.title', 'localized.slug']);

        return DataTables::eloquent($query)
            ->addColumn('category_name', fn (Page $page): string => $page->category?->translations->first()?->name ?? '—')
            ->editColumn('status', fn (Page $page): string => view('pages::pages.partials.status', compact('page'))->render())
            ->addColumn('actions', fn (Page $page): string => view('pages::pages.partials.actions', compact('page'))->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }
}
