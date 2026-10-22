<?php

return [
    'logviewer' => [
        'label' => 'Log İzleme',
        'icon' => 'file-text',
        'route' => 'admin.logviewer.index',
        'active' => 'admin.logviewer.*',
        'permission' => 'logviewer.view',
        'menu_permission' => 'logviewer.menu',
        'order' => 95,
    ],
];
