<?php

return [
    'pages' => [
        'label' => 'Sayfalar',
        'icon' => 'layout-grid',
        'order' => 60,
        'children' => [
            'list' => [
                'label' => 'Sayfalar',
                'icon' => 'layout-grid',
                'route' => 'admin.pages.index',
                'active' => ['admin.pages.index', 'admin.pages.create', 'admin.pages.edit'],
                'permission' => 'pages.view',
                'menu_permission' => 'pages.menu',
                'order' => 5,
            ],
            'categories' => [
                'label' => 'Sayfa kategorileri',
                'icon' => 'category',
                'route' => 'admin.pages.categories.index',
                'active' => 'admin.pages.categories.*',
                'permission' => 'pages.categories.view',
                'menu_permission' => 'pages.categories.menu',
                'order' => 10,
            ],
        ],
    ],
];
