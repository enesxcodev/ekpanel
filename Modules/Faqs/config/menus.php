<?php

return [
    'faqs' => [
        'label' => 'Sıkça Sorulan Sorular',
        'icon' => 'category',
        'order' => 130,
        'children' => [
            'list' => [
                'label' => 'Sorular',
                'icon' => 'layout-grid',
                'route' => 'admin.faqs.index',
                'active' => ['admin.faqs.index', 'admin.faqs.create', 'admin.faqs.edit'],
                'permission' => 'faqs.view',
                'menu_permission' => 'faqs.menu',
                'order' => 5,
            ],
            'categories' => [
                'label' => 'SSS kategorileri',
                'icon' => 'category',
                'route' => 'admin.faqs.categories.index',
                'active' => 'admin.faqs.categories.*',
                'permission' => 'faqs.categories.view',
                'menu_permission' => 'faqs.categories.menu',
                'order' => 10,
            ],
        ],
    ],
];
