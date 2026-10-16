<?php

return [
    'services' => [
        'label' => 'Hizmetler',
        'icon' => 'briefcase',
        'order' => 100,
        'children' => [
            'list' => [
                'label' => 'Listele',
                'icon' => 'briefcase',
                'route' => 'admin.services.index',
                'active' => ['admin.services.index', 'admin.services.create'],
                'permission' => 'services.view',
                'menu_permission' => 'services.menu',
                'order' => 5,
            ],
            'categories' => [
                'label' => 'Kategoriler',
                'icon' => 'category',
                'route' => 'admin.services.categories.index',
                'active' => 'admin.services.categories.*',
                'permission' => 'services.categories.view',
                'menu_permission' => 'services.categories.menu',
                'order' => 10,
            ],
        ],
    ],
];
