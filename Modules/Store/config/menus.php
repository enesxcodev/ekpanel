<?php

return [
    'store' => [
        'label' => 'Store',
        'icon' => 'layout-grid',
        'route' => 'admin.store.index',
        'active' => 'admin.store.*',
        'permission' => 'store.view',
        'menu_permission' => 'store.menu',
        'order' => 100,
    ],
];
