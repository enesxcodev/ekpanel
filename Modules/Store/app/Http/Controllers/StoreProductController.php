<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Store\Http\Requests\SaveStoreProductRequest;
use Modules\Store\Models\StoreCategory;
use Modules\Store\Models\StoreProduct;
use Modules\Store\Services\StoreProductService;

class StoreProductController extends Controller
{
    public function index(): View
    {
        $this->authorize('store.view');

        return view('store::products.index', ['products' => StoreProduct::query()->with('category')->orderBy('sort_order')->orderBy('id')->get()]);
    }

    public function create(): View
    {
        $this->authorize('store.create');

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
        $this->authorize('store.update');

        return $this->form($product);
    }

    public function update(SaveStoreProductRequest $request, StoreProduct $product, StoreProductService $products, NotifyService $notifications): RedirectResponse
    {
        $products->save($request->validated(), $product, $request->user('admin'));
        $notifications->sendToAdminsForActor($request->user('admin'), 'Mağaza ürünü güncellendi', 'Bir mağaza ürünü güncellendi.', route('admin.store.edit', $product, absolute: false));

        return redirect()->route('admin.store.index')->with('notification', ['type' => 'success', 'message' => 'Ürün başarıyla güncellendi.']);
    }

    public function destroy(StoreProduct $product, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('store.delete');
        $product->delete();
        $notifications->sendToAdminsForActor($request->user('admin'), 'Mağaza ürünü silindi', 'Bir mağaza ürünü silindi.', route('admin.store.index', absolute: false));

        return redirect()->route('admin.store.index')->with('notification', ['type' => 'success', 'message' => 'Ürün başarıyla silindi.']);
    }

    private function form(?StoreProduct $product = null): View
    {
        return view($product === null ? 'store::products.create' : 'store::products.edit', ['product' => $product, 'categories' => StoreCategory::query()->orderBy('sort_order')->orderBy('name')->get(['id', 'name'])]);
    }
}
