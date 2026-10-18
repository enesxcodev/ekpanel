<?php

namespace Modules\Faqs\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Faqs\Models\FaqCategory;
use Yajra\DataTables\Facades\DataTables;

class FaqCategoriesDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(FaqCategory::query()
            ->select(['faq_categories.id', 'faq_categories.name', 'faq_categories.created_at'])
            ->withCount('faqs'))
            ->addColumn('actions', fn (FaqCategory $category): string => view('faqs::categories.partials.actions', compact('category'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
