<?php

return [
    'sector' => [
        'label' => 'Sektörler',
        'icon' => 'briefcase',
        'route' => 'admin.sector.index',
        'active' => 'admin.sector.*',
        'permission' => 'sector.view',
        'menu_permission' => 'sector.menu',
        'order' => 120,
    ],
];
