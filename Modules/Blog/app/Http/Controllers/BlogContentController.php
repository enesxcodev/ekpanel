<?php

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Admin\Admin;
use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Blog\DataTables\BlogContentsDataTable;
use Modules\Blog\Http\Requests\SaveBlogContentRequest;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogContent;
use Modules\Blog\Models\BlogContentTranslation;
use Modules\Blog\Models\BlogTag;
use Modules\Blog\Services\BlogLanguageService;
use Modules\Blog\Services\BlogService;

class BlogContentController extends Controller
{
    public function __construct(private MediaService $media, private NotifyService $notifications) {}

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

        $options = $this->formOptions();
        $featureFields = $values->fields($featureForm['fields'], $options['translation']?->extra_fields ?? []);

        return view('blog::contents.create', compact('featureForm', 'featureFields') + $options);
    }

    public function store(SaveBlogContentRequest $request, BlogService $service): RedirectResponse
    {
        $blog = $service->save($request->validated(), null, $request->user('admin'));
        $this->notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Blog yazısı eklendi',
            'Yeni bir blog yazısı eklendi.',
            route('admin.blog.edit', ['blog' => $blog->id, 'lang' => Language::query()->findOrFail($request->validated('lang_id'))->code], absolute: false),
        );

        return redirect()->route('admin.blog.edit', ['blog' => $blog->id, 'lang' => Language::query()->findOrFail($request->validated('lang_id'))->code])->with('notification', ['type' => 'success', 'message' => 'İçerik başarıyla eklendi.']);
    }

    public function edit(BlogContent $blog, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('blog.update');
        $blog->load(['imageMedia', 'tags']);
        $featureForm = $features->form('Blog', 'content');
        $featureFields = $values->fields($featureForm['fields'], $blog->extra_fields ?? []);

        $options = $this->formOptions($blog);
        $featureFields = $values->fields($featureForm['fields'], $options['translation']?->extra_fields ?? []);

        return view('blog::contents.edit', compact('blog', 'featureForm', 'featureFields') + $options);
    }

    public function update(SaveBlogContentRequest $request, BlogContent $blog, BlogService $service): RedirectResponse
    {
        $blog = $service->save($request->validated(), $blog, $request->user('admin'));
        $this->notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Blog yazısı güncellendi',
            'Bir blog yazısı güncellendi.',
            route('admin.blog.edit', ['blog' => $blog->id, 'lang' => Language::query()->findOrFail($request->validated('lang_id'))->code], absolute: false),
        );

        return redirect()->route('admin.blog.edit', ['blog' => $blog->id, 'lang' => Language::query()->findOrFail($request->validated('lang_id'))->code])->with('notification', ['type' => 'success', 'message' => 'İçerik güncellendi.']);
    }

    public function translationEdit(BlogContentTranslation $translation): RedirectResponse
    {
        $this->authorize('blog.update');

        return redirect()->route('admin.blog.edit', ['blog' => $translation->blog_content_id, 'lang' => $translation->language->code]);
    }

    public function destroy(Request $request, BlogContent $blog, BlogService $service): RedirectResponse
    {
        $this->authorize('blog.delete');
        $service->delete($blog);
        $this->notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Blog yazısı silindi',
            'Bir blog yazısı silindi.',
            route('admin.blog.index', absolute: false),
        );

        return redirect()->route('admin.blog.index')->with('notification', ['type' => 'success', 'message' => 'İçerik silindi.']);
    }

    /** @return array<string, mixed> */
    private function formOptions(?BlogContent $blog = null): array
    {
        $languageService = app(BlogLanguageService::class);
        $languages = $languageService->active();
        $selectedLanguage = $languageService->forAdmin(request()->query('lang'));
        $translation = $selectedLanguage ? $blog?->translations()->where('lang_id', $selectedLanguage->id)->first() : null;
        $translatedLanguageIds = $blog?->translations()->pluck('lang_id')->all() ?? [];

        return [
            'languages' => $languages, 'selectedLanguage' => $selectedLanguage, 'translation' => $translation, 'translatedLanguageIds' => $translatedLanguageIds,
            'categoryTree' => $this->categoryTree(),
            'tags' => BlogTag::query()->orderBy('name')->get(['id', 'name']),
            'authors' => Admin::query()->orderBy('name')->get(['id', 'name']),
            'mediaConfig' => $this->media->configuration(auth('admin')->user()),
        ];
    }

    /** @return list<array{id: int, name: string, path: string, depth: int, children: list<array>}> */
    private function categoryTree(): array
    {
        $categories = BlogCategory::query()->orderBy('sort_order')->orderBy('id')->get();
        $byParent = $categories->groupBy(fn (BlogCategory $category): string => (string) ($category->parent_id ?? 0));
        $build = function (?int $parentId, int $depth, string $parentPath = '') use (&$build, $byParent): array {
            $nodes = [];
            foreach ($byParent->get((string) ($parentId ?? 0), collect()) as $category) {
                $name = $category->name ?? 'Çeviri eksik (#'.$category->id.')';
                $path = $parentPath === '' ? $name : $parentPath.' / '.$name;
                $nodes[] = ['id' => $category->id, 'name' => $name, 'path' => $path, 'depth' => $depth, 'children' => $build($category->id, $depth + 1, $path)];
            }

            return $nodes;
        };

        return $build(null, 0);
    }
}
