<?php

namespace Modules\Store\Dashboard\Widgets;

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardWidget;
use Modules\Store\Models\StoreOrder;

class PendingOrdersWidget implements DashboardWidget
{
    /** @return array{id: string, title: string, icon: string, permissions: list<string>, default: bool, order: int, type: string} */
    public function definition(): array
    {
        return [
            'id' => 'store.orders.pending',
            'title' => 'Bekleyen siparişler',
            'icon' => 'briefcase',
            'permissions' => ['store.orders.view'],
            'default' => true,
            'order' => 120,
            'type' => 'stat',
        ];
    }

    /** @return array{value: int, description: string, url: string} */
    public function data(Admin $admin): array
    {
        return [
            'value' => StoreOrder::query()->where('status', 'pending')->count(),
            'description' => 'Ödeme durumundan bağımsız sipariş durumu',
            'url' => route('admin.store.orders.index', absolute: false),
        ];
    }
}
