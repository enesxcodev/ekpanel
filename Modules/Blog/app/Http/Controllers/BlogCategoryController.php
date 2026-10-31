<?php

namespace Modules\Blog\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Settings\Language;
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
use Modules\Blog\Models\BlogCategoryTranslation;
use Modules\Blog\Services\BlogCategoryService;
use Modules\Blog\Services\BlogLanguageService;

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

        $options = $this->formOptions();
        $featureFields = $values->fields($featureForm['fields'], $options['translation']?->extra_fields ?? []);

        return view('blog::categories.create', compact('featureForm', 'featureFields') + $options);
    }

    public function store(SaveBlogCategoryRequest $request, BlogCategoryService $service): RedirectResponse
    {
        $category = $service->save($request->validated(), null, $request->user('admin'));
        $this->notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Blog kategorisi eklendi',
            'Yeni bir blog kategorisi eklendi.',
            route('admin.blog.categories.edit', ['category' => $category->id, 'lang' => Language::query()->findOrFail($request->validated('lang_id'))->code], absolute: false),
        );

        return redirect()->route('admin.blog.categories.edit', ['category' => $category->id, 'lang' => Language::query()->findOrFail($request->validated('lang_id'))->code])->with('notification', ['type' => 'success', 'message' => 'Kategori eklendi.']);
    }

    public function edit(BlogCategory $category, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('blog.categories.update');
        $featureForm = $features->form('Blog', 'category');
        $featureFields = $values->fields($featureForm['fields'], $category->extra_fields ?? []);

        $options = $this->formOptions($category);
        $featureFields = $values->fields($featureForm['fields'], $options['translation']?->extra_fields ?? []);

        return view('blog::categories.edit', compact('category', 'featureForm', 'featureFields') + $options);
    }

    public function update(SaveBlogCategoryRequest $request, BlogCategory $category, BlogCategoryService $service): RedirectResponse
    {
        $category = $service->save($request->validated(), $category, $request->user('admin'));
        $this->notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Blog kategorisi güncellendi',
            'Bir blog kategorisi güncellendi.',
            route('admin.blog.categories.edit', ['category' => $category->id, 'lang' => Language::query()->findOrFail($request->validated('lang_id'))->code], absolute: false),
        );

        return redirect()->route('admin.blog.categories.edit', ['category' => $category->id, 'lang' => Language::query()->findOrFail($request->validated('lang_id'))->code])->with('notification', ['type' => 'success', 'message' => 'Kategori güncellendi.']);
    }

    public function translationEdit(BlogCategoryTranslation $translation): RedirectResponse
    {
        $this->authorize('blog.categories.update');

        return redirect()->route('admin.blog.categories.edit', ['category' => $translation->blog_category_id, 'lang' => $translation->language->code]);
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
    private function formOptions(?BlogCategory $category = null): array
    {
        $languageService = app(BlogLanguageService::class);
        $languages = $languageService->active();
        $selectedLanguage = $languageService->forAdmin(request()->query('lang'));
        $translation = $selectedLanguage ? $category?->translations()->where('lang_id', $selectedLanguage->id)->first() : null;
        $translatedLanguageIds = $category?->translations()->pluck('lang_id')->all() ?? [];

        $categories = BlogCategory::query()->orderBy('id')->get();
        $byId = $categories->keyBy('id');
        foreach ($categories as $item) {
            $item->setRelation('parent', $byId->get($item->parent_id));
        }
        $excludedCategoryIds = $category ? [$category->id, ...$category->descendantIds()] : [];

        return ['languages' => $languages, 'selectedLanguage' => $selectedLanguage, 'translation' => $translation, 'translatedLanguageIds' => $translatedLanguageIds, 'excludedCategoryIds' => $excludedCategoryIds, 'categories' => $categories, 'mediaConfig' => $this->media->configuration(auth('admin')->user())];
    }
}
