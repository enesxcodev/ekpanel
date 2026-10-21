<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Modules\Store\Http\Requests\AddToCartRequest;
use Modules\Store\Http\Requests\CheckoutRequest;
use Modules\Store\Models\StoreProduct;
use Modules\Store\Models\StoreVariant;
use Modules\Store\Services\StoreCartService;
use Modules\Store\Services\StoreOrderService;

class StoreCartController extends Controller
{
    public function index(): View
    {
        return view('store::index');
    }

    public function add(AddToCartRequest $request, StoreCartService $cart): RedirectResponse
    {
        $product = StoreProduct::query()->findOrFail($request->integer('product_id'));
        $variant = $request->filled('variant_id') ? StoreVariant::query()->whereKey($request->integer('variant_id'))->where('store_product_id', $product->id)->firstOrFail() : null;
        abort_unless($product->is_active && ($variant === null || $variant->is_active), 404);
        $cart->add($request->session(), $product, $variant, $request->integer('quantity'));

        return back()->with('notification', ['type' => 'success', 'message' => 'Ürün sepete eklendi.']);
    }

    public function checkout(CheckoutRequest $request, StoreCartService $cart, StoreOrderService $orders): RedirectResponse
    {
        $order = $orders->create($cart->items($request->session()), ['name' => $request->string('customer_name')->toString(), 'email' => $request->string('customer_email')->toString(), 'address' => $request->input('address')], $request->user('web'));
        $cart->clear($request->session());

        return redirect()->route('store.order.complete', $order->number);
    }
}
