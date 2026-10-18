<?php

return [
    'projects' => [
        'label' => 'Projeler', 'icon' => 'briefcase', 'order' => 140,
        'children' => [
            'projects.records' => ['label' => 'Projeler', 'route' => 'admin.projects.index', 'active' => ['admin.projects.index', 'admin.projects.create', 'admin.projects.edit'], 'permission' => 'projects.view', 'menu_permission' => 'projects.menu', 'order' => 10],
            'projects.categories' => ['label' => 'Kategoriler', 'route' => 'admin.projects.categories.index', 'active' => 'admin.projects.categories.*', 'permission' => 'projects.categories.view', 'menu_permission' => 'projects.categories.menu', 'order' => 20],
        ],
    ],
];
