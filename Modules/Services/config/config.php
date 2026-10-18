<?php

use Modules\Services\Services\ServiceCategoryMenuSource;
use Modules\Services\Services\ServiceMenuSource;

return [
    'name' => 'Services',
    'navigation_sources' => [
        'services' => ServiceMenuSource::class,
        'service_categories' => ServiceCategoryMenuSource::class,
    ],
    'icons' => [
        'engine' => 'Motor',
        'computer' => 'Arıza tespit cihazı',
        'bolt' => 'Elektrik',
        'oil' => 'Yağ bakımı',
        'snowflake' => 'Klima',
        'tire' => 'Lastik',
        'exhaust' => 'Egzoz',
        'battery' => 'Akü ve far',
        'briefcase' => 'Çanta',
        'category' => 'Kategori',
        'users' => 'Kullanıcılar',
        'settings' => 'Ayarlar',
        'layout-grid' => 'Izgara',
    ],
];
