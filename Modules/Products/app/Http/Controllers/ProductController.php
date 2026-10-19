<?php

namespace Modules\Products\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Forms\SiteForm;
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

class ProductController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(): View
    {
        $this->authorize('products.view');

        return view('products::index', ['categories' => ProductCategory::query()->orderBy('sort_order')->orderBy('name')->get()]);
    }

    public function data(ProductsDataTable $dataTable): JsonResponse
    {
        $this->authorize('products.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('products.create');

        return $this->form($features, $values);
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

    public function edit(Product $product, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('products.update');

        return $this->form($features, $values, $product);
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

    private function form(ModuleFeatures $features, FeatureValues $values, ?Product $product = null): View
    {
        $product?->load(['imageMedia', 'images']);
        $featureForm = $features->form('Products', 'product');
        $featureFields = $values->fields($featureForm['fields'], $product?->extra_fields ?? []);
        $categories = ProductCategory::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name'])
            ->map(fn (ProductCategory $category): array => ['value' => $category->id, 'label' => $category->name])->all();

        $forms = SiteForm::query()->orderBy('name')->get(['id', 'name'])->map(fn (SiteForm $form): array => ['value' => $form->id, 'label' => $form->name])->all();

        return view($product === null ? 'products::create' : 'products::edit', compact('product', 'featureForm', 'featureFields', 'categories', 'forms') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }
}
