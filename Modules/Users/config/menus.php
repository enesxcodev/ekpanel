<?php

return [
    'users' => [
        'label' => 'Kullanıcılar',
        'icon' => 'users',
        'route' => 'admin.web-users.index',
        'active' => 'admin.web-users.*',
        'permission' => 'users.view',
        'menu_permission' => 'users.menu',
        'order' => 160,
    ],
];
