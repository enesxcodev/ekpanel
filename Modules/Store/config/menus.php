<?php

return ['store' => ['label' => 'Mağaza', 'icon' => 'layout-grid', 'route' => 'admin.store.index', 'active' => 'admin.store.*', 'permission' => 'store.view', 'menu_permission' => 'store.menu', 'order' => 100, 'children' => [
    'products' => ['label' => 'Ürünler', 'route' => 'admin.store.index', 'active' => ['admin.store.index', 'admin.store.create', 'admin.store.edit'], 'permission' => 'store.view', 'menu_permission' => 'store.menu', 'order' => 10],
    'categories' => ['label' => 'Kategoriler', 'route' => 'admin.store.categories.index', 'active' => 'admin.store.categories.*', 'permission' => 'store.categories.view', 'menu_permission' => 'store.categories.menu', 'order' => 20],
    'orders' => ['label' => 'Siparişler', 'route' => 'admin.store.orders.index', 'active' => 'admin.store.orders.*', 'permission' => 'store.orders.view', 'menu_permission' => 'store.orders.menu', 'order' => 30],
    'stock' => ['label' => 'Stok hareketleri', 'route' => 'admin.store.stock.index', 'active' => 'admin.store.stock.*', 'permission' => 'store.stock.view', 'menu_permission' => 'store.stock.menu', 'order' => 40],
]]];
