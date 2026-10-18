<?php

namespace Modules\Documents\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Documents\Models\DocumentCategory;
use Yajra\DataTables\Facades\DataTables;

class DocumentCategoriesDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(DocumentCategory::query()
            ->select(['document_categories.id', 'document_categories.name', 'document_categories.icon', 'document_categories.created_at'])
            ->withCount('documents'))
            ->addColumn('actions', fn (DocumentCategory $category): string => view('documents::categories.partials.actions', compact('category'))->render())
            ->rawColumns(['actions'])
            ->toJson();
    }
}
