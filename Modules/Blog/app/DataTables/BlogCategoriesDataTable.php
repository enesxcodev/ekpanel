<?php

namespace Modules\Blog\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Services\BlogLanguageService;
use Yajra\DataTables\Facades\DataTables;

class BlogCategoriesDataTable
{
    public function handle(): JsonResponse
    {
        $language = app(BlogLanguageService::class)->forAdmin(request()->query('lang'));
        request()->attributes->set('blog_language', $language?->code ?? 'tr');

        return DataTables::eloquent(BlogCategory::query()->leftJoin('blog_category_translations as localized', function ($join) use ($language): void {
            $join->on('localized.blog_category_id', '=', 'blog_categories.id')->where('localized.lang_id', '=', $language?->id ?? 0);
        })->select(['blog_categories.*', 'localized.name as name', 'localized.slug as slug'])->with('parent')->withCount(['children', 'contents']))
            ->editColumn('name', fn ($record): string => $record->name ?? 'Çeviri eksik (#'.$record->id.')')
            ->filterColumn('name', fn ($query, $keyword) => $query->where('localized.name', 'like', '%'.$keyword.'%'))
            ->orderColumn('name', 'localized.name $1')
            ->filterColumn('slug', fn ($query, $keyword) => $query->where('localized.slug', 'like', '%'.$keyword.'%'))
            ->orderColumn('slug', 'localized.slug $1')
            ->addColumn('parent_name', fn (BlogCategory $category): string => $category->parent?->name ?? 'â€”')
            ->addColumn('actions', fn (BlogCategory $category): string => view('blog::categories.partials.actions', compact('category'))->render())
            ->removeColumn('translations')
            ->rawColumns(['actions'])->toJson();
    }
}
