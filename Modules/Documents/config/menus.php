<?php

return [
    'documents' => [
        'label' => 'Belgeler',
        'icon' => 'category',
        'order' => 140,
        'children' => [
            'list' => [
                'label' => 'Belgeler',
                'icon' => 'layout-grid',
                'route' => 'admin.documents.index',
                'active' => ['admin.documents.index', 'admin.documents.create', 'admin.documents.edit'],
                'permission' => 'documents.view',
                'menu_permission' => 'documents.menu',
                'order' => 5,
            ],
            'categories' => [
                'label' => 'Belge kategorileri',
                'icon' => 'category',
                'route' => 'admin.documents.categories.index',
                'active' => 'admin.documents.categories.*',
                'permission' => 'documents.categories.view',
                'menu_permission' => 'documents.categories.menu',
                'order' => 10,
            ],
        ],
    ],
];
