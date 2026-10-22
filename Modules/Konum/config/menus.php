<?php

return [
    'konum' => [
        'label' => 'Türkiye Konum',
        'icon' => 'map-pin',
        'route' => 'admin.konum.index',
        'active' => 'admin.konum.*',
        'permission' => 'konum.view',
        'menu_permission' => 'konum.menu',
        'order' => 160,
    ],
];
