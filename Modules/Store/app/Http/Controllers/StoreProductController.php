<?php

namespace Modules\Store\Http\Controllers;

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
use Illuminate\Support\Facades\Storage;
use Modules\Store\Http\Requests\SaveStoreProductRequest;
use Modules\Store\Models\StoreCategory;
use Modules\Store\Models\StoreProduct;
use Modules\Store\Models\StoreProductTranslation;
use Modules\Store\Services\StoreLanguageService;
use Modules\Store\Services\StoreMoney;
use Modules\Store\Services\StoreProductService;
use Nwidart\Modules\Facades\Module;
use Yajra\DataTables\Facades\DataTables;

class StoreProductController extends Controller
{
    public function index(Request $request, StoreLanguageService $languages): View
    {
        $language = $languages->forAdmin($request->query('lang'));

        return view('store::products.index', ['dataUrl' => route('admin.store.data', ['lang' => $language?->code ?? 'tr'])]);
    }

    public function data(): JsonResponse
    {
        $language = app(StoreLanguageService::class)->forAdmin(request()->query('lang'));
        $query = StoreProduct::query()->where('is_archived', false)->select('store_products.*')->with(['translation', 'variants' => fn ($query) => $query->where('is_active', true)->where('is_archived', false)])->withSum(['variants' => fn ($query) => $query->where('is_active', true)->where('is_archived', false)], 'stock_quantity');

        return DataTables::eloquent($query)
            ->addColumn('title', fn (StoreProduct $product): string => $product->translation?->title ?? __('store::store.translation_missing'))
            ->filterColumn('title', fn ($query, string $keyword) => $query->whereHas('translations', fn ($translation) => $translation->where('lang_id', $language?->id)->where('title', 'like', '%'.$keyword.'%')))
            ->orderColumn('title', fn ($query, string $order) => $query->orderBy(StoreProductTranslation::query()->select('title')->whereColumn('store_product_id', 'store_products.id')->where('lang_id', $language?->id), $order))
            ->addColumn('stock_summary', fn (StoreProduct $product): string => ! $product->tracks_stock ? 'Takip kapalı' : (string) ($product->has_variants ? $product->variants_sum_stock_quantity : $product->stock_quantity).($product->has_variants ? ' (varyant)' : ''))
            ->editColumn('price_minor', fn (StoreProduct $product): string => StoreMoney::format($product->price_minor, $product->currency))
            ->editColumn('is_active', fn (StoreProduct $product): string => $product->is_active ? 'Yayında' : 'Taslak')
            ->addColumn('actions', fn (StoreProduct $product): string => view('store::products.partials.actions', ['product' => $product, 'languageCode' => $language?->code ?? 'tr'])->render())
            ->rawColumns(['actions'])->toJson();
    }

    public function catalog(Request $request): JsonResponse
    {
        abort_unless((Module::find('Products')?->isEnabled() ?? false) && $request->user('admin')->can('products.view') && $request->user('admin')->can('media.view'), 403);
        $data = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'page' => ['nullable', 'integer', 'min:1']]);
        $class = 'Modules\\Products\\Models\\Product';
        abort_unless(class_exists($class), 404);
        $language = app(StoreLanguageService::class)->forFrontend((string) $request->query('lang', 'tr'));
        $languageId = $language?->id;
        request()->attributes->set('products_language_id', $languageId);
        $products = $class::query()->with(['imageMedia', 'images', 'translations' => fn ($query) => $query->where('lang_id', $languageId)])
            ->whereHas('translations', fn ($query) => $query->where('lang_id', $languageId))
            ->when(filled($data['q'] ?? null), fn ($query) => $query->where(fn ($query) => $query->whereHas('translations', fn ($translation) => $translation->where('lang_id', $languageId)->where('title', 'like', '%'.$data['q'].'%'))->orWhere('id', $data['q'])))->orderBy('id')->paginate(10);

        return response()->json(['items' => $products->map(function ($product) use ($language): array {
            $translation = $product->translations->first();

            return ['id' => $product->id, 'title' => $translation?->title, 'slug' => $translation?->slug, 'short_description' => $translation?->short_description, 'content' => $translation?->content, 'image_media_id' => $product->image_media_id, 'image_url' => $product->image_url, 'images' => $product->images->map(fn ($image): array => ['id' => $image->id, 'name' => $image->name, 'url' => Storage::disk($image->disk)->url($image->path)])->all(), 'existing_url' => ($existing = StoreProduct::query()->where('source_product_id', $product->id)->first()) ? route('admin.store.edit', ['product' => $existing, 'lang' => $language?->code ?? 'tr']) : null];
        }), 'next' => $products->nextPageUrl()]);
    }

    public function create(Request $request, StoreLanguageService $languages): View
    {
        return $this->form(null, $languages->forAdmin($request->query('lang')));
    }

    public function store(SaveStoreProductRequest $request, StoreProductService $products, NotifyService $notifications): RedirectResponse
    {
        $product = $products->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor($request->user('admin'), 'Mağaza ürünü eklendi', 'Bir mağaza ürünü eklendi.', route('admin.store.edit', $product, absolute: false));

        return redirect()->route('admin.store.edit', ['product' => $product, 'lang' => $request->input('lang_id') ? Language::query()->find($request->input('lang_id'))?->code : 'tr'])->with('notification', ['type' => 'success', 'message' => 'Ürün başarıyla eklendi.']);
    }

    public function edit(Request $request, StoreProduct $product, StoreLanguageService $languages): View
    {
        return $this->form($product, $languages->forAdmin($request->query('lang')));
    }

    public function update(SaveStoreProductRequest $request, StoreProduct $product, StoreProductService $products, NotifyService $notifications): RedirectResponse
    {
        $products->save($request->validated(), $product, $request->user('admin'));
        $notifications->sendToAdminsForActor($request->user('admin'), 'Mağaza ürünü güncellendi', 'Bir mağaza ürünü güncellendi.', route('admin.store.edit', $product, absolute: false));

        return redirect()->route('admin.store.edit', ['product' => $product, 'lang' => Language::query()->find($request->input('lang_id'))?->code ?? 'tr'])->with('notification', ['type' => 'success', 'message' => 'Ürün başarıyla güncellendi.']);
    }

    public function destroy(StoreProduct $product, Request $request, StoreProductService $products, NotifyService $notifications): RedirectResponse
    {
        $products->delete($product);
        $notifications->sendToAdminsForActor($request->user('admin'), 'Mağaza ürünü arşivlendi', 'Bir mağaza ürünü arşivlendi.', route('admin.store.index', absolute: false));

        return redirect()->route('admin.store.index')->with('notification', ['type' => 'success', 'message' => 'Ürün arşivlendi.']);
    }

    private function form(?StoreProduct $product = null, ?Language $language = null): View
    {
        $product?->load(['imageMedia', 'shareImageMedia', 'images', 'translations', 'variants' => fn ($query) => $query->where('is_archived', false)]);
        $language ??= app(StoreLanguageService::class)->forAdmin();
        $translation = $product?->translations->firstWhere('lang_id', $language?->id);
        if ($product && $translation) {
            $product->setRelation('translation', $translation);
        }
        $languages = app(StoreLanguageService::class)->active()->map(fn ($item): array => ['code' => $item->code, 'name' => $item->name ?? strtoupper($item->code), 'has_translation' => $product?->translations->contains('lang_id', $item->id) ?? false])->all();
        $featureForm = app(ModuleFeatures::class)->form('Store', 'product');
        $featureFields = app(FeatureValues::class)->fields($featureForm['fields'], $product?->extra_fields ?? []);
        $categories = StoreCategory::query()->with('translations')->orderBy('sort_order')->get()->map(function (StoreCategory $category) use ($language): array {
            $translation = $category->translations->firstWhere('lang_id', $language?->id);

            return ['value' => $translation ? $category->id : 'missing-'.$category->id, 'label' => $translation?->name ?? ('Kategori #'.$category->id.' — '.__('store::store.translation_missing_for_language'))];
        })->all();
        $mediaConfig = app(MediaService::class)->configuration(auth('admin')->user());
        $canImport = $product === null && (Module::find('Products')?->isEnabled() ?? false) && auth('admin')->user()->can('products.view') && auth('admin')->user()->can('media.view');
        $variants = $product?->variants->map(fn ($variant): array => ['id' => $variant->id, 'sku' => $variant->sku, 'option_values' => $variant->option_values, 'price' => $variant->price_minor === null ? '' : StoreMoney::decimal($variant->price_minor), 'stock_quantity' => $variant->stock_quantity, 'image_media_id' => $variant->image_media_id, 'is_active' => (int) $variant->is_active])->all() ?? [];

        return view($product === null ? 'store::products.create' : 'store::products.edit', compact('product', 'categories', 'featureForm', 'featureFields', 'mediaConfig', 'canImport', 'variants', 'languages', 'language'));
    }
}
