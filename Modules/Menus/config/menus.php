<?php

return [
    'menus' => [
        'label' => 'Menüler',
        'icon' => 'layout-grid',
        'route' => 'admin.menus.index',
        'active' => 'admin.menus.*',
        'permission' => 'menus.view',
        'menu_permission' => 'menus.menu',
        'order' => 65,
    ],
];
