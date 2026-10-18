<?php

return [
    'testimonials' => [
        'label' => 'Müşteri yorumları',
        'icon' => 'message-quote',
        'route' => 'admin.testimonials.index',
        'active' => 'admin.testimonials.*',
        'permission' => 'testimonials.view',
        'menu_permission' => 'testimonials.menu',
        'order' => 120,
    ],
];
