<?php

return [
    'galeriler' => [
        'label' => 'Galeriler', 'icon' => 'photo', 'order' => 130,
        'children' => [
            'galeriler.records' => ['label' => 'Fotoğraf ve videolar', 'icon' => 'photo', 'route' => 'admin.galeriler.index', 'active' => ['admin.galeriler.index', 'admin.galeriler.create', 'admin.galeriler.edit'], 'permission' => 'galeriler.view', 'menu_permission' => 'galeriler.menu', 'order' => 10],
            'galeriler.categories' => ['label' => 'Kategoriler', 'icon' => 'category', 'route' => 'admin.galeriler.categories.index', 'active' => 'admin.galeriler.categories.*', 'permission' => 'galeriler.categories.view', 'menu_permission' => 'galeriler.categories.menu', 'order' => 20],
        ],
    ],
];
