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
use Modules\Pages\DataTables\PageCategoriesDataTable;
use Modules\Pages\Http\Requests\SavePageCategoryRequest;
use Modules\Pages\Models\PageCategory;
use Modules\Pages\Services\PageCategoryService;
use Modules\Pages\Services\PagesLanguageService;

class PageCategoryController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(PagesLanguageService $languages, Request $request): View
    {
        $this->authorize('pages.categories.view');

        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);

        return view('pages::categories.index', compact('activeLanguages', 'selectedLanguage'));
    }

    public function data(PageCategoriesDataTable $dataTable): JsonResponse
    {
        $this->authorize('pages.categories.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values, PagesLanguageService $languages, Request $request): View
    {
        $this->authorize('pages.categories.create');
        $featureForm = $features->form('Pages', 'category');
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);
        $featureFields = $values->fields($featureForm['fields'], []);
        $categoryOptions = $this->categoryOptions(null, $selectedLanguage?->id);

        return view('pages::categories.create', compact('featureForm', 'featureFields', 'categoryOptions', 'activeLanguages', 'selectedLanguage') + ['translatedLanguageIds' => [], 'translation' => null, 'mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(SavePageCategoryRequest $request, PageCategoryService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->save($request->validated(), null, $request->user('admin'));
        $langCode = $category->translations->firstWhere('lang_id', $request->validated('lang_id'))?->language?->code;
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Sayfa kategorisi eklendi',
            'Bir sayfa kategorisi eklendi.',
            route('admin.pages.categories.edit', ['category' => $category->id, 'lang' => $langCode], absolute: false),
        );

        return redirect()->route('admin.pages.categories.edit', ['category' => $category->id, 'lang' => $langCode])->with('notification', ['type' => 'success', 'message' => 'Sayfa kategorisi başarıyla eklendi.']);
    }

    public function edit(PageCategory $category, ModuleFeatures $features, FeatureValues $values, PagesLanguageService $languages, Request $request): View
    {
        $this->authorize('pages.categories.update');
        $featureForm = $features->form('Pages', 'category');
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);
        $translation = $selectedLanguage ? $category->translations()->where('lang_id', $selectedLanguage->id)->first() : null;
        $translatedLanguageIds = $languages->translatedLanguageIds($category);
        $featureFields = $values->fields($featureForm['fields'], $translation?->extra_fields ?? []);
        $categoryOptions = $this->categoryOptions($category, $selectedLanguage?->id);

        return view('pages::categories.edit', compact('category', 'featureForm', 'featureFields', 'categoryOptions', 'activeLanguages', 'selectedLanguage', 'translation', 'translatedLanguageIds') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function update(SavePageCategoryRequest $request, PageCategory $category, PageCategoryService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->save($request->validated(), $category, $request->user('admin'));
        $langCode = $category->translations->firstWhere('lang_id', $request->validated('lang_id'))?->language?->code;
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Sayfa kategorisi güncellendi',
            'Bir sayfa kategorisi güncellendi.',
            route('admin.pages.categories.edit', ['category' => $category->id, 'lang' => $langCode], absolute: false),
        );

        return redirect()->route('admin.pages.categories.edit', ['category' => $category->id, 'lang' => $langCode])->with('notification', ['type' => 'success', 'message' => 'Sayfa kategorisi başarıyla güncellendi.']);
    }

    public function destroy(PageCategory $category, PageCategoryService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('pages.categories.delete');
        $deleted = $service->delete($category);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Sayfa kategorisi silindi',
            'Bir sayfa kategorisi silindi.',
            route('admin.pages.categories.index', absolute: false),
        );

        return redirect()->route('admin.pages.categories.index')->with('notification', [
            'type' => $deleted ? 'success' : 'error',
            'message' => $deleted ? 'Sayfa kategorisi başarıyla silindi.' : 'Sayfa veya alt kategori içeren kategori silinemez.',
        ]);
    }

    /** @return list<array{value: int, label: string}> */
    private function categoryOptions(?PageCategory $excluded = null, ?int $languageId = null): array
    {
        $excludedIds = $excluded === null ? [] : [$excluded->id, ...$excluded->descendantIds()];
        $categories = PageCategory::query()->with(['translations' => fn ($query) => $query->where('lang_id', $languageId)])
            ->whereNotIn('id', $excludedIds)->orderBy('sort_order')->orderBy('id')->get()
            ->filter(fn (PageCategory $category): bool => $category->translations->isNotEmpty());
        $byParent = $categories->groupBy(fn (PageCategory $category): string => (string) ($category->parent_id ?? 0));
        $options = [];
        $append = function (?int $parentId, int $depth) use (&$append, &$options, $byParent): void {
            foreach ($byParent->get((string) ($parentId ?? 0), collect()) as $category) {
                $options[] = ['value' => $category->id, 'label' => str_repeat('— ', $depth).($category->translations->first()?->name ?? $category->name)];
                $append($category->id, $depth + 1);
            }
        };
        $append(null, 0);

        return $options;
    }
}
