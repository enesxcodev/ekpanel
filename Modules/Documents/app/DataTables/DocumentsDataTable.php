<?php

namespace Modules\Documents\DataTables;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Modules\Documents\Models\Document;
use Yajra\DataTables\Facades\DataTables;

class DocumentsDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(Document::query()
            ->with('category:id,name')
            ->select(['documents.id', 'documents.category_id', 'documents.name', 'documents.content', 'documents.slug', 'documents.file', 'documents.status', 'documents.created_at']))
            ->addColumn('category_name', fn (Document $document): string => $document->category?->name ?? 'Kategorisiz')
            ->editColumn('content', fn (Document $document): string => Str::limit(trim(strip_tags((string) $document->content)), 180))
            ->editColumn('status', fn (Document $document): string => $document->status === 'published' ? 'Yayında' : 'Taslak')
            ->editColumn('file', fn (Document $document): string => view('documents::partials.file', compact('document'))->render())
            ->addColumn('actions', fn (Document $document): string => view('documents::partials.actions', compact('document'))->render())
            ->rawColumns(['file', 'actions'])
            ->toJson();
    }
}
