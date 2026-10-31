<?php

namespace Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Products\DataTables\ProductCategoriesDataTable;
use Modules\Products\Http\Requests\StoreProductCategoryRequest;
use Modules\Products\Http\Requests\UpdateProductCategoryRequest;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Services\ProductCategoryService;
use Modules\Products\Services\ProductsLanguageService;

class ProductCategoryController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(ProductsLanguageService $languages, Request $request): View
    {
        $this->authorize('products.categories.view');

        $selectedLanguage = $languages->forAdmin($request->query('lang'));

        return view('products::categories.index', compact('selectedLanguage'));
    }

    public function data(ProductCategoriesDataTable $dataTable): JsonResponse
    {
        $this->authorize('products.categories.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values, ProductsLanguageService $languages, Request $request): View
    {
        $this->authorize('products.categories.create');

        $featureForm = $features->form('Products', 'category');
        $featureFields = $values->fields($featureForm['fields'], []);
        $languages = $languages->active();
        $selectedLanguage = app(ProductsLanguageService::class)->forAdmin($request->query('lang'));
        $translation = null;
        $translatedLanguageIds = [];

        return view('products::categories.create', compact('featureForm', 'featureFields', 'languages', 'selectedLanguage', 'translation', 'translatedLanguageIds') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(StoreProductCategoryRequest $request, ProductCategoryService $product, NotifyService $notifications): RedirectResponse
    {
        $category = $product->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Ürün kategorisi eklendi',
            'Bir ürün kategorisi eklendi.',
            route('admin.products.categories.edit', ['category' => $category->id], absolute: false),
        );

        return redirect()->route('admin.products.categories.index')->with('notification', [
            'type' => 'success', 'message' => 'Ürün kategorisi başarıyla eklendi.',
        ]);
    }

    public function edit(ProductCategory $category, ModuleFeatures $features, FeatureValues $values, ProductsLanguageService $languages, Request $request): View
    {
        $this->authorize('products.categories.update');

        $featureForm = $features->form('Products', 'category');
        $languages = $languages->active();
        $selectedLanguage = app(ProductsLanguageService::class)->forAdmin($request->query('lang'));
        $category->load('translations');
        $translation = $category->translations->firstWhere('lang_id', $selectedLanguage?->id);
        $featureFields = $values->fields($featureForm['fields'], $translation?->extra_fields ?? []);
        $translatedLanguageIds = $category->translations->pluck('lang_id')->all();

        return view('products::categories.edit', compact('category', 'translation', 'featureForm', 'featureFields', 'languages', 'selectedLanguage', 'translatedLanguageIds') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $category, ProductCategoryService $product, NotifyService $notifications): RedirectResponse
    {
        $category = $product->save($request->validated(), $category, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Ürün kategorisi güncellendi',
            'Bir ürün kategorisi güncellendi.',
            route('admin.products.categories.edit', ['category' => $category->id], absolute: false),
        );

        return redirect()->route('admin.products.categories.index')->with('notification', [
            'type' => 'success', 'message' => 'Ürün kategorisi başarıyla güncellendi.',
        ]);
    }

    public function destroy(ProductCategory $category, ProductCategoryService $product, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('products.categories.delete');
        $product->delete($category);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Ürün kategorisi silindi',
            'Bir ürün kategorisi silindi.',
            route('admin.products.categories.index', absolute: false),
        );

        return redirect()->route('admin.products.categories.index')->with('notification', [
            'type' => 'success', 'message' => 'Ürün kategorisi başarıyla silindi.',
        ]);
    }
}
