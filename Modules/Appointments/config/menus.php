<?php

return [
    'appointments' => [
        'label' => 'Randevular',
        'icon' => 'calendar-event',
        'route' => 'admin.appointments.index',
        'active' => 'admin.appointments.*',
        'permission' => 'appointments.view',
        'menu_permission' => 'appointments.menu',
        'order' => 110,
    ],
];
