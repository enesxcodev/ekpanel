<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Store\Http\Requests\SaveStoreProductRequest;
use Modules\Store\Models\StoreCategory;
use Modules\Store\Models\StoreProduct;
use Modules\Store\Services\StoreMoney;
use Modules\Store\Services\StoreProductService;
use Nwidart\Modules\Facades\Module;
use Yajra\DataTables\Facades\DataTables;

class StoreProductController extends Controller
{
    public function index(): View
    {
        return view('store::products.index');
    }

    public function data(): JsonResponse
    {
        return DataTables::eloquent(StoreProduct::query()->where('is_archived', false)->select('store_products.*')->withSum(['variants' => fn ($query) => $query->where('is_active', true)->where('is_archived', false)], 'stock_quantity'))
            ->addColumn('stock_summary', fn (StoreProduct $product): string => ! $product->tracks_stock ? 'Takip kapalı' : (string) ($product->has_variants ? $product->variants_sum_stock_quantity : $product->stock_quantity).($product->has_variants ? ' (varyant)' : ''))
            ->editColumn('price_minor', fn (StoreProduct $product): string => StoreMoney::format($product->price_minor, $product->currency))
            ->editColumn('is_active', fn (StoreProduct $product): string => $product->is_active ? 'Yayında' : 'Taslak')
            ->addColumn('actions', fn (StoreProduct $product): string => view('store::products.partials.actions', compact('product'))->render())
            ->rawColumns(['actions'])->toJson();
    }

    public function catalog(Request $request): JsonResponse
    {
        abort_unless((Module::find('Products')?->isEnabled() ?? false) && $request->user('admin')->can('products.view') && $request->user('admin')->can('media.view'), 403);
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'min:1']]);
        $class = 'Modules\\Products\\Models\\Product';
        abort_unless(class_exists($class), 404);
        $products = $class::query()->with(['imageMedia', 'images'])->when(filled($data['q'] ?? null), fn ($query) => $query->where(fn ($query) => $query->where('title', 'like', '%'.$data['q'].'%')->orWhere('id', $data['q'])))->orderBy('id')->paginate(10);

        return response()->json(['items' => $products->map(fn ($product): array => ['id' => $product->id, 'title' => $product->title, 'slug' => $product->slug, 'short_description' => $product->short_description, 'content' => $product->content, 'image_media_id' => $product->image_media_id, 'image_url' => $product->image_url, 'images' => $product->images->map(fn ($image): array => ['id' => $image->id, 'name' => $image->name, 'url' => Storage::disk($image->disk)->url($image->path)])->all(), 'existing_url' => ($existing = StoreProduct::query()->where('source_product_id', $product->id)->first()) ? route('admin.store.edit', $existing) : null]), 'next' => $products->nextPageUrl()]);
    }

    public function create(): View
    {
        return $this->form();
    }

    public function store(SaveStoreProductRequest $request, StoreProductService $products, NotifyService $notifications): RedirectResponse
    {
        $product = $products->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor($request->user('admin'), 'Mağaza ürünü eklendi', 'Bir mağaza ürünü eklendi.', route('admin.store.edit', $product, absolute: false));

        return redirect()->route('admin.store.index')->with('notification', ['type' => 'success', 'message' => 'Ürün başarıyla eklendi.']);
    }

    public function edit(StoreProduct $product): View
    {
        return $this->form($product);
    }

    public function update(SaveStoreProductRequest $request, StoreProduct $product, StoreProductService $products, NotifyService $notifications): RedirectResponse
    {
        $products->save($request->validated(), $product, $request->user('admin'));
        $notifications->sendToAdminsForActor($request->user('admin'), 'Mağaza ürünü güncellendi', 'Bir mağaza ürünü güncellendi.', route('admin.store.edit', $product, absolute: false));

        return redirect()->route('admin.store.index')->with('notification', ['type' => 'success', 'message' => 'Ürün başarıyla güncellendi.']);
    }

    public function destroy(StoreProduct $product, Request $request, StoreProductService $products, NotifyService $notifications): RedirectResponse
    {
        $products->delete($product);
        $notifications->sendToAdminsForActor($request->user('admin'), 'Mağaza ürünü arşivlendi', 'Bir mağaza ürünü arşivlendi.', route('admin.store.index', absolute: false));

        return redirect()->route('admin.store.index')->with('notification', ['type' => 'success', 'message' => 'Ürün arşivlendi.']);
    }

    private function form(?StoreProduct $product = null): View
    {
        $product?->load(['imageMedia', 'shareImageMedia', 'images', 'variants' => fn ($query) => $query->where('is_archived', false)]);
        $featureForm = app(ModuleFeatures::class)->form('Store', 'product');
        $featureFields = app(FeatureValues::class)->fields($featureForm['fields'], $product?->extra_fields ?? []);
        $categories = StoreCategory::query()->orderBy('sort_order')->get()->map(fn ($category): array => ['value' => $category->id, 'label' => $category->name])->all();
        $mediaConfig = app(MediaService::class)->configuration(auth('admin')->user());
        $canImport = $product === null && (Module::find('Products')?->isEnabled() ?? false) && auth('admin')->user()->can('products.view') && auth('admin')->user()->can('media.view');
        $variants = $product?->variants->map(fn ($variant): array => ['id' => $variant->id, 'sku' => $variant->sku, 'option_values' => $variant->option_values, 'price' => $variant->price_minor === null ? '' : StoreMoney::decimal($variant->price_minor), 'stock_quantity' => $variant->stock_quantity, 'image_media_id' => $variant->image_media_id, 'is_active' => (int) $variant->is_active])->all() ?? [];

        return view($product === null ? 'store::products.create' : 'store::products.edit', compact('product', 'categories', 'featureForm', 'featureFields', 'mediaConfig', 'canImport', 'variants'));
    }
}
