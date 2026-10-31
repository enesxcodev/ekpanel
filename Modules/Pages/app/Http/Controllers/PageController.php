<?php

namespace Modules\Pages\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Pages\DataTables\PagesDataTable;
use Modules\Pages\Http\Requests\SavePageRequest;
use Modules\Pages\Models\Page;
use Modules\Pages\Models\PageCategory;
use Modules\Pages\Services\PageService;
use Modules\Pages\Services\PagesLanguageService;

class PageController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(PagesLanguageService $languages, Request $request): View
    {
        $this->authorize('pages.view');

        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);

        return view('pages::pages.index', compact('activeLanguages', 'selectedLanguage'));
    }

    public function data(PagesDataTable $dataTable): JsonResponse
    {
        $this->authorize('pages.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values, PagesLanguageService $languages, Request $request): View
    {
        $this->authorize('pages.create');
        $featureForm = $features->form('Pages', 'page');
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);
        $featureFields = $values->fields($featureForm['fields'], []);
        $categoryTree = $this->categoryTree($selectedLanguage?->id);

        return view('pages::pages.create', compact('featureForm', 'featureFields', 'categoryTree', 'activeLanguages', 'selectedLanguage') + ['translatedLanguageIds' => [], 'translation' => null, 'mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(SavePageRequest $request, PageService $service, NotifyService $notifications): RedirectResponse
    {
        $page = $service->save($request->validated(), null, $request->user('admin'));
        $langCode = $page->translations->firstWhere('lang_id', $request->validated('lang_id'))?->language?->code;
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Sayfa eklendi',
            'Bir sayfa eklendi.',
            route('admin.pages.edit', ['page' => $page->id, 'lang' => $langCode], absolute: false),
        );

        return redirect()->route('admin.pages.edit', ['page' => $page->id, 'lang' => $langCode])->with('notification', ['type' => 'success', 'message' => 'Sayfa başarıyla eklendi.']);
    }

    public function edit(Page $page, ModuleFeatures $features, FeatureValues $values, PagesLanguageService $languages, Request $request): View
    {
        $this->authorize('pages.update');
        $featureForm = $features->form('Pages', 'page');
        $page->load('imageMedia');
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);
        $translation = $selectedLanguage ? $page->translations()->where('lang_id', $selectedLanguage->id)->first() : null;
        $translatedLanguageIds = $languages->translatedLanguageIds($page);
        $featureFields = $values->fields($featureForm['fields'], $translation?->extra_fields ?? []);
        $categoryTree = $this->categoryTree($selectedLanguage?->id);

        return view('pages::pages.edit', compact('page', 'featureForm', 'featureFields', 'categoryTree', 'activeLanguages', 'selectedLanguage', 'translation', 'translatedLanguageIds') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function update(SavePageRequest $request, Page $page, PageService $service, NotifyService $notifications): RedirectResponse
    {
        $page = $service->save($request->validated(), $page, $request->user('admin'));
        $langCode = $page->translations->firstWhere('lang_id', $request->validated('lang_id'))?->language?->code;
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Sayfa güncellendi',
            'Bir sayfa güncellendi.',
            route('admin.pages.edit', ['page' => $page->id, 'lang' => $langCode], absolute: false),
        );

        return redirect()->route('admin.pages.edit', ['page' => $page->id, 'lang' => $langCode])->with('notification', ['type' => 'success', 'message' => 'Sayfa başarıyla güncellendi.']);
    }

    public function destroy(Page $page, PageService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('pages.delete');
        $service->delete($page);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Sayfa silindi',
            'Bir sayfa silindi.',
            route('admin.pages.index', absolute: false),
        );

        return redirect()->route('admin.pages.index')->with('notification', ['type' => 'success', 'message' => 'Sayfa başarıyla silindi.']);
    }

    public function show(string $path, PageService $service, Request $request, PagesLanguageService $languages): View
    {
        return $this->renderPublicPage($path, (string) $request->route('lang', PagesLanguageService::PrimaryCode), $service, $request, $languages);
    }

    public function showLocalized(string $lang, string $path, PageService $service, Request $request, PagesLanguageService $languages): View
    {
        return $this->renderPublicPage($path, $lang, $service, $request, $languages);
    }

    private function renderPublicPage(string $path, string $langCode, PageService $service, Request $request, PagesLanguageService $languages): View
    {
        $language = $languages->forPublic($langCode);
        $segments = array_values(array_filter(explode('/', trim($path, '/')), fn (string $segment): bool => $segment !== ''));
        abort_if($segments === [], 404);

        $pageSlug = array_pop($segments);
        $parentId = null;
        $category = null;
        $categoryChain = [];
        foreach ($segments as $categorySlug) {
            $category = PageCategory::query()
                ->where('parent_id', $parentId)
                ->whereHas('translations', fn ($query) => $query->where('lang_id', $language->id)->where('slug', $categorySlug))
                ->first();
            abort_if($category === null, 404);
            $categoryChain[] = $category;
            $parentId = $category->id;
        }

        $query = Page::query()
            ->whereHas('translations', fn ($query) => $query->where('lang_id', $language->id)->where('slug', $pageSlug))
            ->where('status', 'published')
            ->with(['category.parent.parent', 'imageMedia']);
        $category === null
            ? $query->whereNull('page_category_id')
            : $query->where('page_category_id', $category->id);
        $page = $query->firstOrFail();
        $translation = $page->translations()->where('lang_id', $language->id)->firstOrFail();
        foreach ($categoryChain as $categoryItem) {
            $categoryTranslation = $categoryItem->translations()->where('lang_id', $language->id)->firstOrFail();
            $categoryItem->setAttribute('name', $categoryTranslation->name);
            $categoryItem->setAttribute('slug', $categoryTranslation->slug);
        }
        $page->setRelation('category', $category);
        foreach (['title', 'slug', 'short_description', 'content', 'meta_title', 'meta_keywords', 'meta_description', 'extra_fields'] as $field) {
            $page->setAttribute($field, $translation->{$field});
        }

        return view('pages::pages.show', [
            'page' => $page,
            'contentLanguage' => $language,
            'safeContent' => $service->sanitizeContent((string) $page->content),
        ]);
    }

    /** @return list<array{id: int, name: string, path: string, depth: int, children: list<array>}> */
    private function categoryTree(?int $languageId = null): array
    {
        $categories = PageCategory::query()->with(['translations' => fn ($query) => $query->where('lang_id', $languageId)])->orderBy('sort_order')->orderBy('id')->get()
            ->filter(fn (PageCategory $category): bool => $category->translations->isNotEmpty());
        $byParent = $categories->groupBy(fn (PageCategory $category): string => (string) ($category->parent_id ?? 0));
        $build = function (?int $parentId, int $depth, string $parentPath = '') use (&$build, $byParent): array {
            $nodes = [];
            foreach ($byParent->get((string) ($parentId ?? 0), collect()) as $category) {
                $label = $category->translations->first()?->name ?? $category->name;
                $path = $parentPath === '' ? $label : $parentPath.' / '.$label;
                $nodes[] = [
                    'id' => $category->id,
                    'name' => $label,
                    'path' => $path,
                    'depth' => $depth,
                    'children' => $build($category->id, $depth + 1, $path),
                ];
            }

            return $nodes;
        };

        return $build(null, 0);
    }
}
