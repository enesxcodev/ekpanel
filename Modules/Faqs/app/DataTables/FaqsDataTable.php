<?php

namespace Modules\Faqs\DataTables;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Modules\Faqs\Models\Faq;
use Yajra\DataTables\Facades\DataTables;

class FaqsDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(Faq::query()
            ->with('category:id,name')
            ->select(['faqs.id', 'faqs.category_id', 'faqs.name', 'faqs.content', 'faqs.created_at']))
            ->addColumn('category_name', fn (Faq $faq): string => $faq->category?->name ?? 'Kategorisiz')
            ->editColumn('content', fn (Faq $faq): string => Str::limit(trim(strip_tags($faq->content)), 180))
            ->addColumn('actions', fn (Faq $faq): string => view('faqs::partials.actions', compact('faq'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
