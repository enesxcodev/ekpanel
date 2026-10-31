<?php

namespace Modules\Blog\DataTables;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogContent;
use Modules\Blog\Services\BlogLanguageService;
use Yajra\DataTables\Facades\DataTables;

class BlogContentsDataTable
{
    public function handle(Request $request): JsonResponse
    {
        $language = app(BlogLanguageService::class)->forAdmin(request()->query('lang'));
        request()->attributes->set('blog_language', $language?->code ?? 'tr');
        $query = BlogContent::query()->with(['category.parent.parent', 'author'])->leftJoin('blog_content_translations as localized', function ($join) use ($language): void {
            $join->on('localized.blog_content_id', '=', 'blog_contents.id')->where('localized.lang_id', '=', $language?->id ?? 0);
        })->select(['blog_contents.*', 'localized.title as title', 'localized.slug as slug']);
        if ($request->filled('category_id')) {
            $category = BlogCategory::query()->find($request->integer('category_id'));
            if ($category !== null) {
                $query->whereIn('blog_category_id', [$category->id, ...$category->descendantIds()]);
            }
        }
        if ($request->filled('tag_id')) {
            $query->whereHas('tags', fn ($tags) => $tags->where('blog_tags.id', $request->integer('tag_id')));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }
        foreach (['date_from' => '>=', 'date_to' => '<='] as $parameter => $operator) {
            if ($request->filled($parameter) && preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $request->input($parameter)) === 1) {
                $value = $parameter === 'date_to' ? $request->input($parameter).' 23:59:59' : $request->input($parameter).' 00:00:00';
                $query->where('published_at', $operator, $value);
            }
        }

        return DataTables::eloquent($query)
            ->editColumn('title', fn ($record): string => $record->title ?? 'Çeviri eksik (#'.$record->id.')')
            ->filterColumn('title', fn ($query, $keyword) => $query->where('localized.title', 'like', '%'.$keyword.'%'))
            ->orderColumn('title', 'localized.title $1')
            ->filterColumn('slug', fn ($query, $keyword) => $query->where('localized.slug', 'like', '%'.$keyword.'%'))
            ->orderColumn('slug', 'localized.slug $1')
            ->addColumn('category_path', fn (BlogContent $content): string => $content->category?->path ?? 'â€”')
            ->addColumn('author_name', fn (BlogContent $content): string => $content->author?->name ?? 'â€”')
            ->editColumn('status', fn (BlogContent $content): string => view('blog::contents.partials.status', compact('content'))->render())
            ->editColumn('published_at', fn (BlogContent $content): string => $content->published_at?->format('Y-m-d H:i') ?? 'â€”')
            ->addColumn('actions', fn (BlogContent $content): string => view('blog::contents.partials.actions', compact('content'))->render())
            ->removeColumn('translations')
            ->rawColumns(['status', 'actions'])
            ->toJson();
    }
}
