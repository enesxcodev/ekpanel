<?php

return [
    'services' => [
        'label' => 'Hizmetler',
        'icon' => 'briefcase',
        'order' => 100,
        'children' => [
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
