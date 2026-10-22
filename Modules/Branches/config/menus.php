<?php

return [
    'branches' => [
        'label' => 'Şubeler / Bayiler',
        'icon' => 'map-pin',
        'order' => 85,
        'children' => [
            'list' => [
                'label' => 'Listele',
                'icon' => 'map-pin',
                'route' => 'admin.branches.index',
                'active' => ['admin.branches.index', 'admin.branches.create', 'admin.branches.edit'],
                'permission' => 'branches.view',
                'menu_permission' => 'branches.menu',
                'order' => 5,
            ],
            'categories' => [
                'label' => 'Kategoriler',
                'icon' => 'category',
                'route' => 'admin.branches.categories.index',
                'active' => 'admin.branches.categories.*',
                'permission' => 'branches.categories.view',
                'menu_permission' => 'branches.categories.menu',
                'order' => 10,
            ],
        ],
    ],
];
