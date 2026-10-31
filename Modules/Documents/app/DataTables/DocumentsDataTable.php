<?php

namespace Modules\Documents\DataTables;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Modules\Documents\Models\Document;
use Modules\Documents\Services\DocumentLanguageService;
use Yajra\DataTables\Facades\DataTables;

class DocumentsDataTable
{
    public function handle(): JsonResponse
    {
        $language = app(DocumentLanguageService::class)->selectedAdmin(request());
        $languageId = $language?->id;
        $languageCode = $language?->code;
        $query = Document::query()
            ->leftJoin('document_translations as localized', function ($join) use ($languageId): void {
                $join->on('localized.document_id', '=', 'documents.id')->where('localized.lang_id', $languageId);
            })
            ->leftJoin('document_category_translations as localized_category', function ($join) use ($languageId): void {
                $join->on('localized_category.document_category_id', '=', 'documents.category_id')->where('localized_category.lang_id', $languageId);
            })
            ->select(['documents.id', 'documents.category_id', 'documents.file', 'documents.status', 'documents.created_at', 'localized.name as name', 'localized.content as content', 'localized.slug as slug', 'localized_category.name as category_name']);

        return DataTables::eloquent($query)
            ->editColumn('name', function (Document $document) use ($languageCode): string {
                if ($document->name !== null) {
                    return e($document->name);
                }

                return '<a href="'.e(route('admin.documents.edit', ['document' => $document->id, 'lang' => $languageCode])).'">Çeviri eksik (#'.$document->id.')</a>';
            })
            ->editColumn('slug', fn (Document $document): string => e((string) $document->slug))
            ->editColumn('category_name', function (Document $document) use ($languageCode): string {
                if ($document->category_id === null) {
                    return 'Kategorisiz';
                }
                if ($document->category_name !== null) {
                    return e($document->category_name);
                }

                return '<a href="'.e(route('admin.documents.categories.edit', ['category' => $document->category_id, 'lang' => $languageCode])).'">Çeviri eksik (#'.$document->category_id.')</a>';
            })
            ->editColumn('content', fn (Document $document): string => e(Str::limit(trim(strip_tags((string) $document->content)), 180)))
            ->editColumn('status', fn (Document $document): string => $document->status === 'published' ? 'Yayında' : 'Taslak')
            ->editColumn('file', fn (Document $document): string => view('documents::partials.file', compact('document'))->render())
            ->addColumn('actions', fn (Document $document): string => view('documents::partials.actions', ['document' => $document, 'languageCode' => $languageCode])->render())
            ->rawColumns(['name', 'category_name', 'file', 'actions'])
            ->toJson();
    }
}
