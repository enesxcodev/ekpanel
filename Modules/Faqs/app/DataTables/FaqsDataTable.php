<?php

namespace Modules\Faqs\DataTables;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Modules\Faqs\Models\Faq;
use Modules\Faqs\Services\FaqLanguageService;
use Yajra\DataTables\Facades\DataTables;

class FaqsDataTable
{
    public function handle(): JsonResponse
    {
        $language = app(FaqLanguageService::class)->forAdmin(request()->query('lang'));
        $languageId = $language?->id ?? 0;
        $query = Faq::query()->leftJoin('faq_translations as localized', function ($join) use ($languageId): void {
            $join->on('localized.faq_id', '=', 'faqs.id')->where('localized.lang_id', '=', $languageId);
        })->select(['faqs.id', 'faqs.category_id', 'faqs.created_at', 'localized.name as localized_name', 'localized.content as localized_content'])
            ->with(['category.translations' => fn ($query) => $query->where('lang_id', $languageId)]);

        return DataTables::eloquent($query)
            ->addColumn('name', fn (Faq $faq): string => $faq->localized_name ?? 'Çeviri eksik (#'.$faq->id.')')
            ->filterColumn('name', fn ($query, $keyword) => $query->where('localized.name', 'like', '%'.$keyword.'%'))
            ->orderColumn('name', 'localized.name $1')
            ->addColumn('category_name', fn (Faq $faq): string => $faq->category?->name ?? ($faq->category_id === null ? 'Kategorisiz' : 'Çeviri eksik (#'.$faq->category_id.')'))
            ->addColumn('content', fn (Faq $faq): string => $faq->localized_content === null ? 'Çeviri eksik (#'.$faq->id.')' : Str::limit(trim(strip_tags($faq->localized_content)), 180))
            ->filterColumn('content', fn ($query, $keyword) => $query->where('localized.content', 'like', '%'.$keyword.'%'))
            ->orderColumn('content', 'localized.content $1')
            ->addColumn('actions', fn (Faq $faq): string => view('faqs::partials.actions', ['faq' => $faq, 'langCode' => $language?->code])->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
