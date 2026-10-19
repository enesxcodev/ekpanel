<?php

return [
    'popup' => [
        'label' => 'Popup',
        'icon' => 'layout-grid',
        'route' => 'admin.popup.index',
        'active' => 'admin.popup.*',
        'permission' => 'popup.view',
        'menu_permission' => 'popup.menu',
        'order' => 100,
    ],
];
