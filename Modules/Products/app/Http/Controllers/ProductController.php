<?php

namespace Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Forms\SiteForm;
use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Products\DataTables\ProductsDataTable;
use Modules\Products\Http\Requests\StoreProductRequest;
use Modules\Products\Http\Requests\UpdateProductRequest;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Services\ProductService;
use Modules\Products\Services\ProductsLanguageService;

class ProductController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(ProductsLanguageService $languages, Request $request): View
    {
        $this->authorize('products.view');

        $selectedLanguage = $languages->forAdmin($request->query('lang'));
        $categories = ProductCategory::query()->with(['translations' => fn ($query) => $query->where('lang_id', $selectedLanguage?->id)])->whereHas('translations', fn ($query) => $query->where('lang_id', $selectedLanguage?->id))->orderBy('sort_order')->get();

        return view('products::index', compact('categories', 'selectedLanguage'));
    }

    public function data(ProductsDataTable $dataTable): JsonResponse
    {
        $this->authorize('products.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values, ProductsLanguageService $languages, Request $request): View
    {
        $this->authorize('products.create');

        return $this->form($features, $values, null, $languages->forAdmin($request->query('lang')));
    }

    public function store(StoreProductRequest $request, ProductService $product, NotifyService $notifications): RedirectResponse
    {
        $product = $product->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Ürün eklendi',
            'Bir ürün eklendi.',
            route('admin.products.edit', ['product' => $product->id], absolute: false),
        );

        return redirect()->route('admin.products.index')->with('notification', [
            'type' => 'success', 'message' => 'Ürün başarıyla eklendi.',
        ]);
    }

    public function edit(Product $product, ModuleFeatures $features, FeatureValues $values, ProductsLanguageService $languages, Request $request): View
    {
        $this->authorize('products.update');

        return $this->form($features, $values, $product, $languages->forAdmin($request->query('lang')));
    }

    public function update(UpdateProductRequest $request, Product $product, ProductService $manager, NotifyService $notifications): RedirectResponse
    {
        $product = $manager->save($request->validated(), $product, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Ürün güncellendi',
            'Bir ürün güncellendi.',
            route('admin.products.edit', ['product' => $product->id], absolute: false),
        );

        return redirect()->route('admin.products.index')->with('notification', ['type' => 'success', 'message' => 'Ürün başarıyla güncellendi.']);
    }

    public function destroy(Product $product, ProductService $manager, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('products.delete');
        $manager->delete($product);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Ürün silindi',
            'Bir ürün silindi.',
            route('admin.products.index', absolute: false),
        );

        return redirect()->route('admin.products.index')->with('notification', ['type' => 'success', 'message' => 'Ürün başarıyla silindi.']);
    }

    private function form(ModuleFeatures $features, FeatureValues $values, ?Product $product = null, ?Language $selectedLanguage = null): View
    {
        $languages = app(ProductsLanguageService::class)->active();
        $product?->load(['imageMedia', 'images', 'translations']);
        $translation = $product?->translations->firstWhere('lang_id', $selectedLanguage?->id);
        $featureForm = $features->form('Products', 'product');
        $featureFields = $values->fields($featureForm['fields'], $translation?->extra_fields ?? []);
        $categories = ProductCategory::query()->with(['translations' => fn ($query) => $query->where('lang_id', $selectedLanguage?->id)])->orderBy('sort_order')->get()
            ->map(fn (ProductCategory $category): array => ['value' => $category->id, 'label' => $category->name ?? 'Kategori #'.$category->id.' (bu dilde çeviri yok)'])->all();

        $forms = SiteForm::query()->orderBy('name')->get(['id', 'name'])->map(fn (SiteForm $form): array => ['value' => $form->id, 'label' => $form->name])->all();

        $translatedLanguageIds = $product?->translations->pluck('lang_id')->all() ?? [];

        return view($product === null ? 'products::create' : 'products::edit', compact('product', 'translation', 'featureForm', 'featureFields', 'categories', 'forms', 'languages', 'selectedLanguage', 'translatedLanguageIds') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }
}
