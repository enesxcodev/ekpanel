<?php

return [
    'team' => [
        'label' => 'Ekipler',
        'icon' => 'users',
        'route' => 'admin.team.index',
        'active' => 'admin.team.*',
        'permission' => 'team.view',
        'menu_permission' => 'team.menu',
        'order' => 110,
    ],
];
