<?php

return [
    'events' => [
        'label' => 'Etkinlikler',
        'icon' => 'calendar',
        'route' => 'admin.events.index',
        'active' => 'admin.events.*',
        'permission' => 'events.view',
        'menu_permission' => 'events.menu',
        'order' => 120,
    ],
];
