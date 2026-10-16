<?php

namespace Modules\Pages\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Pages\Models\Page;
use Yajra\DataTables\Facades\DataTables;

class PagesDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(Page::query()->with('category:id,name')->select(['id', 'page_category_id', 'title', 'slug', 'status', 'sort_order']))
            ->addColumn('category_name', fn (Page $page): string => $page->category?->name ?? '—')
            ->editColumn('status', fn (Page $page): string => view('pages::pages.partials.status', compact('page'))->render())
            ->addColumn('actions', fn (Page $page): string => view('pages::pages.partials.actions', compact('page'))->render())
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }
}
