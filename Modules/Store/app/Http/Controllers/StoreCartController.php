<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Settings\SettingsService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Modules\Store\Http\Requests\AddToCartRequest;
use Modules\Store\Http\Requests\CheckoutRequest;
use Modules\Store\Models\StoreOrder;
use Modules\Store\Services\StoreCartService;
use Modules\Store\Services\StoreOrderService;
use Nwidart\Modules\Facades\Module;

class StoreCartController extends Controller
{
    public function index(Request $request, StoreCartService $cart): View
    {
        $items = $cart->items($request->session());
        $quote = null;
        $cartError = null;
        try {
            $quote = $cart->quote($items);
        } catch (ValidationException $error) {
            $cartError = collect($error->errors())->flatten()->first();
        }

        return view('store::public.cart', compact('items', 'quote', 'cartError'));
    }

    public function add(AddToCartRequest $request, StoreCartService $cart): RedirectResponse
    {
        $cart->add($request->session(), $request->integer('product_id'), $request->filled('variant_id') ? $request->integer('variant_id') : null, $request->integer('quantity'));

        return redirect()->route('store.cart');
    }

    public function update(Request $request, StoreCartService $cart, int $index): RedirectResponse
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:0', 'max:100']]);
        $cart->update($request->session(), $index, (int) $data['quantity']);

        return redirect()->route('store.cart');
    }

    public function form(Request $request, StoreCartService $cart, SettingsService $settings): View
    {
        $guest = (bool) $settings->get('store.guest_checkout_enabled', false);
        $users = (Module::find('Users')?->isEnabled() ?? false);
        if (! $guest && (! $users || ! $request->user('web'))) {
            return view('store::public.login', compact('users'));
        }
        $data = $request->validate(['delivery_method' => ['nullable', 'in:shipping,pickup']]);
        $delivery = $data['delivery_method'] ?? 'shipping';
        $quote = $cart->quote($cart->items($request->session()), $delivery);
        abort_if($quote['lines'] === [], 422, 'Sepetiniz boş.');
        $key = $request->session()->get('store.checkout_key') ?? (string) Str::uuid();
        if (StoreOrder::query()->where('checkout_key', $key)->exists()) {
            $key = (string) Str::uuid();
        }
        $request->session()->put('store.checkout_key', $key);
        $pickup = (bool) $settings->get('store.pickup_enabled', false);
        $instructions = (string) $settings->get('store.manual_payment_instructions', '');

        return view('store::public.checkout', compact('quote', 'key', 'pickup', 'instructions', 'delivery'));
    }

    public function checkout(CheckoutRequest $request, StoreCartService $cart, StoreOrderService $orders): RedirectResponse
    {
        $data = $request->validated();
        abort_unless(hash_equals((string) $request->session()->get('store.checkout_key'), $data['checkout_key']), 419);
        $result = (function () use ($request, $cart, $orders, $data): StoreOrder {
            $existing = StoreOrder::query()->where('checkout_key', $data['checkout_key'])->first();
            $items = $cart->items($request->session());
            if ($existing && $items === []) {
                abort_unless($request->session()->get('store.completed.'.$existing->id) === $existing->access_token, 403);

                $items = $existing->lines->map(fn ($line): array => ['product_id' => $line->store_product_id, 'variant_id' => $line->store_variant_id, 'quantity' => $line->quantity])->all();
            }

            return $orders->create($items, $data, $request->user('web'));
        })();
        $request->session()->put('store.completed.'.$result->id, $result->access_token);
        $cart->clear($request->session());

        return redirect()->route('store.order.complete', ['number' => $result->number, 'token' => $result->access_token]);
    }

    public function complete(Request $request, string $number, string $token): Response
    {
        $order = StoreOrder::query()->with('lines')->where('number', $number)->where('access_token', $token)->firstOrFail();

        return response()->view('store::public.order', compact('order'))->header('Cache-Control', 'no-store, private')->header('Referrer-Policy', 'no-referrer');
    }

    public function account(Request $request): View
    {
        abort_unless((Module::find('Users')?->isEnabled() ?? false) && $request->user('web'), 403);
        $orders = StoreOrder::query()->where('user_id', $request->user('web')->id)->latest()->paginate(20);

        return view('store::public.orders', compact('orders'));
    }

    public function accountOrder(Request $request, string $number): View
    {
        abort_unless((Module::find('Users')?->isEnabled() ?? false) && $request->user('web'), 403);
        $order = StoreOrder::query()->with('lines')->where('user_id', $request->user('web')->id)->where('number', $number)->firstOrFail();

        return view('store::public.order', compact('order'));
    }
}
