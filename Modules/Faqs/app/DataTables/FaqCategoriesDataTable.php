<?php

namespace Modules\Faqs\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Faqs\Models\FaqCategory;
use Modules\Faqs\Services\FaqLanguageService;
use Yajra\DataTables\Facades\DataTables;

class FaqCategoriesDataTable
{
    public function handle(): JsonResponse
    {
        $language = app(FaqLanguageService::class)->forAdmin(request()->query('lang'));
        $languageId = $language?->id ?? 0;
        $query = FaqCategory::query()->leftJoin('faq_category_translations as localized', function ($join) use ($languageId): void {
            $join->on('localized.faq_category_id', '=', 'faq_categories.id')->where('localized.lang_id', '=', $languageId);
        })->select(['faq_categories.id', 'faq_categories.created_at', 'localized.name as localized_name'])
            ->withCount('faqs');

        return DataTables::eloquent($query)
            ->addColumn('name', fn (FaqCategory $category): string => $category->localized_name ?? 'Çeviri eksik (#'.$category->id.')')
            ->filterColumn('name', fn ($query, $keyword) => $query->where('localized.name', 'like', '%'.$keyword.'%'))
            ->orderColumn('name', 'localized.name $1')
            ->addColumn('actions', fn (FaqCategory $category): string => view('faqs::categories.partials.actions', ['category' => $category, 'langCode' => $language?->code])->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
