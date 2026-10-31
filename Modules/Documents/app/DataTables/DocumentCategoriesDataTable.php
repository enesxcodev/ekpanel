<?php

namespace Modules\Documents\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Documents\Models\DocumentCategory;
use Modules\Documents\Services\DocumentLanguageService;
use Yajra\DataTables\Facades\DataTables;

class DocumentCategoriesDataTable
{
    public function handle(): JsonResponse
    {
        $language = app(DocumentLanguageService::class)->selectedAdmin(request());
        $languageId = $language?->id;
        $languageCode = $language?->code;

        return DataTables::eloquent(DocumentCategory::query()
            ->leftJoin('document_category_translations as localized', function ($join) use ($languageId): void {
                $join->on('localized.document_category_id', '=', 'document_categories.id')->where('localized.lang_id', $languageId);
            })
            ->select(['document_categories.id', 'document_categories.icon', 'document_categories.created_at', 'localized.name as name'])
            ->withCount('documents'))
            ->editColumn('name', function (DocumentCategory $category) use ($languageCode): string {
                if ($category->name !== null) {
                    return e($category->name);
                }

                return '<a href="'.e(route('admin.documents.categories.edit', ['category' => $category->id, 'lang' => $languageCode])).'">Çeviri eksik (#'.$category->id.')</a>';
            })
            ->addColumn('actions', fn (DocumentCategory $category): string => view('documents::categories.partials.actions', ['category' => $category, 'languageCode' => $languageCode])->render())
            ->rawColumns(['name', 'actions'])
            ->toJson();
    }
}
