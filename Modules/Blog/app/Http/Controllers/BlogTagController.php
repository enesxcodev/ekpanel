<?php

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Blog\DataTables\BlogTagsDataTable;
use Modules\Blog\Http\Requests\SaveBlogTagRequest;
use Modules\Blog\Models\BlogTag;

class BlogTagController extends Controller
{
    public function index(): View
    {
        $this->authorize('blog.tags.view');

        return view('blog::tags.index');
    }

    public function data(BlogTagsDataTable $table): JsonResponse
    {
        $this->authorize('blog.tags.view');

        return $table->handle();
    }

    public function create(): View
    {
        $this->authorize('blog.tags.create');

        return view('blog::tags.create');
    }

    public function edit(BlogTag $tag): View
    {
        $this->authorize('blog.tags.update');

        return view('blog::tags.edit', compact('tag'));
    }

    public function store(SaveBlogTagRequest $request): RedirectResponse
    {
        BlogTag::query()->create($request->validated());

        return redirect()->route('admin.blog.tags.index')->with('notification', ['type' => 'success', 'message' => 'Etiket eklendi.']);
    }

    public function update(SaveBlogTagRequest $request, BlogTag $tag): RedirectResponse
    {
        $tag->update($request->validated());

        return redirect()->route('admin.blog.tags.index')->with('notification', ['type' => 'success', 'message' => 'Etiket güncellendi.']);
    }

    public function destroy(BlogTag $tag): RedirectResponse
    {
        $this->authorize('blog.tags.delete');
        $tag->delete();

        return redirect()->route('admin.blog.tags.index')->with('notification', ['type' => 'success', 'message' => 'Etiket silindi.']);
    }
}
