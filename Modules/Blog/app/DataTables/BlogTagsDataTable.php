<?php

namespace Modules\Blog\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Blog\Models\BlogTag;
use Yajra\DataTables\Facades\DataTables;

class BlogTagsDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(BlogTag::query()->select(['id', 'name', 'slug', 'created_at'])->withCount('contents'))
            ->addColumn('actions', fn (BlogTag $tag): string => view('blog::tags.partials.actions', compact('tag'))->render())
            ->rawColumns(['actions'])->toJson();
    }
}
