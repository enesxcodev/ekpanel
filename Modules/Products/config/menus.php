<?php

return [
    'products' => [
        'label' => 'Ürünler',
        'icon' => 'briefcase',
        'order' => 100,
        'children' => [
            'list' => [
                'label' => 'Listele',
                'icon' => 'briefcase',
                'route' => 'admin.products.index',
                'active' => ['admin.products.index', 'admin.products.create', 'admin.products.edit'],
                'permission' => 'products.view',
                'menu_permission' => 'products.menu',
                'order' => 5,
            ],
            'categories' => [
                'label' => 'Kategoriler',
                'icon' => 'category',
                'route' => 'admin.products.categories.index',
                'active' => 'admin.products.categories.*',
                'permission' => 'products.categories.view',
                'menu_permission' => 'products.categories.menu',
                'order' => 10,
            ],
        ],
    ],
];
