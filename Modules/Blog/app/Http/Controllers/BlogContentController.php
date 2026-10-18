<?php

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin\Admin;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Blog\DataTables\BlogContentsDataTable;
use Modules\Blog\Http\Requests\SaveBlogContentRequest;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogContent;
use Modules\Blog\Models\BlogTag;
use Modules\Blog\Services\BlogService;

class BlogContentController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(): View
    {
        $this->authorize('blog.view');

        return view('blog::contents.index', ['categoryTree' => $this->categoryTree(), 'tags' => BlogTag::query()->orderBy('name')->get(['id', 'name']), 'statuses' => ['draft' => 'Taslak', 'published' => 'Yayında', 'archived' => 'Arşiv']]);
    }

    public function data(BlogContentsDataTable $table, Request $request): JsonResponse
    {
        $this->authorize('blog.view');

        return $table->handle($request);
    }

    public function create(ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('blog.create');
        $featureForm = $features->form('Blog', 'content');
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('blog::contents.create', compact('featureForm', 'featureFields') + $this->formOptions());
    }

    public function store(SaveBlogContentRequest $request, BlogService $service): RedirectResponse
    {
        $service->save($request->validated(), null, $request->user('admin'));

        return redirect()->route('admin.blog.index')->with('notification', ['type' => 'success', 'message' => 'İçerik başarıyla eklendi.']);
    }

    public function edit(BlogContent $blog, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('blog.update');
        $blog->load(['imageMedia', 'tags']);
        $featureForm = $features->form('Blog', 'content');
        $featureFields = $values->fields($featureForm['fields'], $blog->extra_fields ?? []);

        return view('blog::contents.edit', compact('blog', 'featureForm', 'featureFields') + $this->formOptions());
    }

    public function update(SaveBlogContentRequest $request, BlogContent $blog, BlogService $service): RedirectResponse
    {
        $service->save($request->validated(), $blog, $request->user('admin'));

        return redirect()->route('admin.blog.index')->with('notification', ['type' => 'success', 'message' => 'İçerik güncellendi.']);
    }

    public function destroy(BlogContent $blog, BlogService $service): RedirectResponse
    {
        $this->authorize('blog.delete');
        $service->delete($blog);

        return redirect()->route('admin.blog.index')->with('notification', ['type' => 'success', 'message' => 'İçerik silindi.']);
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            'categoryTree' => $this->categoryTree(),
            'tags' => BlogTag::query()->orderBy('name')->get(['id', 'name']),
            'authors' => Admin::query()->orderBy('name')->get(['id', 'name']),
            'mediaConfig' => $this->media->configuration(auth('admin')->user()),
        ];
    }

    /** @return list<array{id: int, name: string, path: string, depth: int, children: list<array>}> */
    private function categoryTree(): array
    {
        $categories = BlogCategory::query()->orderBy('sort_order')->orderBy('name')->get();
        $byParent = $categories->groupBy(fn (BlogCategory $category): string => (string) ($category->parent_id ?? 0));
        $build = function (?int $parentId, int $depth, string $parentPath = '') use (&$build, $byParent): array {
            $nodes = [];
            foreach ($byParent->get((string) ($parentId ?? 0), collect()) as $category) {
                $path = $parentPath === '' ? $category->name : $parentPath.' / '.$category->name;
                $nodes[] = ['id' => $category->id, 'name' => $category->name, 'path' => $path, 'depth' => $depth, 'children' => $build($category->id, $depth + 1, $path)];
            }

            return $nodes;
        };

        return $build(null, 0);
    }
}
