<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\HtmlSanitizer;
use App\Services\Settings\SeoMetadata;
use App\Services\Settings\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Store\Services\StoreFrontendService;
use Modules\Store\Services\StoreLanguageService;
use Modules\Store\Services\StoreMoney;

class PublicStoreController extends Controller
{
    public function index(Request $request, StoreFrontendService $frontend): View|RedirectResponse
    {
        if ($request->route('lang') === StoreLanguageService::PrimaryCode) {
            return redirect()->route('store.index', $request->query(), 301);
        }
        $data = $request->validate(['category_id' => ['nullable', 'integer', 'min:1'], 'q' => ['nullable', 'string', 'max:100']]);

        $language = app(StoreLanguageService::class)->forPublic((string) ($request->route('lang') ?? $request->query('lang', 'tr')));

        return view('store::public.index', ['products' => $frontend->products(isset($data['category_id']) ? (int) $data['category_id'] : null, $data['q'] ?? null, 12, $language?->code), 'categories' => $frontend->categories($language?->code), 'taxIncluded' => (bool) app(SettingsService::class)->get('store.tax_included', true), 'languageCode' => $language?->code ?? 'tr']);
    }

    public function show(Request $request, string $slug, StoreFrontendService $frontend, HtmlSanitizer $sanitizer, SeoMetadata $seo): View|RedirectResponse
    {
        if ($request->route('lang') === StoreLanguageService::PrimaryCode) {
            return redirect()->route('store.show', ['slug' => $slug, ...$request->query()], 301);
        }
        $language = app(StoreLanguageService::class)->forPublic((string) ($request->route('lang') ?? $request->query('lang', 'tr')));
        $product = $frontend->findBySlug($slug, $language?->code);
        abort_unless($product, 404);
        $metadata = $seo->build(['page' => $product]);
        if ($image = $product->shareImageMedia) {
            $url = url(Storage::disk($image->disk)->url($image->path));
            $metadata['og']['image'] = $url;
            $metadata['twitter']['image'] = $url;
        }
        $variants = $product->variants->map(fn ($variant): array => ['id' => $variant->id, 'options' => $variant->option_values, 'price' => StoreMoney::format($variant->price_minor ?? $product->price_minor, $product->currency), 'stock' => $variant->stock_quantity, 'available' => ! $product->tracks_stock || $product->allow_backorders || $variant->stock_quantity > 0, 'image' => $variant->imageMedia ? Storage::disk($variant->imageMedia->disk)->url($variant->imageMedia->path) : $product->image_url])->all();

        return view('store::public.show', ['product' => $product, 'safeContent' => $sanitizer->sanitize($product->content ?? ''), 'seoMetadata' => $metadata, 'variantData' => $variants, 'taxIncluded' => (bool) app(SettingsService::class)->get('store.tax_included', true)]);
    }
}
