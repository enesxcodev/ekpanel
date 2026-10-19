<?php

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Blog\DataTables\BlogCategoriesDataTable;
use Modules\Blog\Http\Requests\SaveBlogCategoryRequest;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Services\BlogCategoryService;

class BlogCategoryController extends Controller
{
    public function __construct(private MediaService $media, private NotifyService $notifications) {}

    public function index(): View
    {
        $this->authorize('blog.categories.view');

        return view('blog::categories.index');
    }

    public function data(BlogCategoriesDataTable $table): JsonResponse
    {
        $this->authorize('blog.categories.view');

        return $table->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('blog.categories.create');
        $featureForm = $features->form('Blog', 'category');
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('blog::categories.create', compact('featureForm', 'featureFields') + $this->formOptions());
    }

    public function store(SaveBlogCategoryRequest $request, BlogCategoryService $service): RedirectResponse
    {
        $category = $service->save($request->validated(), null, $request->user('admin'));
        $this->notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Blog kategorisi eklendi',
            'Yeni bir blog kategorisi eklendi.',
            route('admin.blog.categories.edit', ['category' => $category->id], absolute: false),
        );

        return redirect()->route('admin.blog.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori eklendi.']);
    }

    public function edit(BlogCategory $category, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('blog.categories.update');
        $featureForm = $features->form('Blog', 'category');
        $featureFields = $values->fields($featureForm['fields'], $category->extra_fields ?? []);

        return view('blog::categories.edit', compact('category', 'featureForm', 'featureFields') + $this->formOptions());
    }

    public function update(SaveBlogCategoryRequest $request, BlogCategory $category, BlogCategoryService $service): RedirectResponse
    {
        $category = $service->save($request->validated(), $category, $request->user('admin'));
        $this->notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Blog kategorisi güncellendi',
            'Bir blog kategorisi güncellendi.',
            route('admin.blog.categories.edit', ['category' => $category->id], absolute: false),
        );

        return redirect()->route('admin.blog.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori güncellendi.']);
    }

    public function destroy(Request $request, BlogCategory $category, BlogCategoryService $service): RedirectResponse
    {
        $this->authorize('blog.categories.delete');
        $deleted = $service->delete($category);
        if ($deleted) {
            $this->notifications->sendToAdminsForActor(
                $request->user('admin'),
                'Blog kategorisi silindi',
                'Bir blog kategorisi silindi.',
                route('admin.blog.categories.index', absolute: false),
            );
        }

        return redirect()->route('admin.blog.categories.index')->with('notification', ['type' => $deleted ? 'success' : 'warning', 'message' => $deleted ? 'Kategori silindi.' : 'İçerik veya alt kategori bulunan kategori silinemez. Önce içeriği taşıyın veya alt kategorileri düzenleyin.']);
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        $categories = BlogCategory::query()->orderBy('name')->get();

        return ['categories' => $categories, 'mediaConfig' => $this->media->configuration(auth('admin')->user())];
    }
}
