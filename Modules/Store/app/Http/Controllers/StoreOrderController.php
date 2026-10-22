<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Store\Http\Requests\UpdateStoreOrderRequest;
use Modules\Store\Models\StockMovement;
use Modules\Store\Models\StoreOrder;
use Modules\Store\Services\StoreOrderService;
use Yajra\DataTables\Facades\DataTables;

class StoreOrderController extends Controller
{
    public function index(): View
    {
        return view('store::orders.index');
    }

    public function data(): JsonResponse
    {
        return DataTables::eloquent(StoreOrder::query())->addColumn('actions', fn ($order): string => view('store::orders.actions', compact('order'))->render())->rawColumns(['actions'])->toJson();
    }

    public function show(StoreOrder $order): View
    {
        return view('store::orders.show', ['order' => $order->load('lines')]);
    }

    public function update(UpdateStoreOrderRequest $request, StoreOrder $order, StoreOrderService $orders, NotifyService $notifications): RedirectResponse
    {
        $orders->transition($order, $request->validated(), $request->user('admin'));
        $notifications->sendToAdminsForActor($request->user('admin'), 'Mağaza siparişi güncellendi', 'Sipariş durumu güncellendi.', route('admin.store.orders.show', $order, absolute: false));

        return back()->with('notification', ['type' => 'success', 'message' => 'Sipariş güncellendi.']);
    }

    public function stock(): View
    {
        return view('store::stock.index');
    }

    public function stockData(): JsonResponse
    {
        return DataTables::eloquent(StockMovement::query()->with(['product', 'variant']))->addColumn('product_title', fn ($movement): string => $movement->product?->title ?? 'Arşiv ürün')->toJson();
    }
}
