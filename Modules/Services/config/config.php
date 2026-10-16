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
        'briefcase' => 'Çanta',
        'category' => 'Kategori',
        'users' => 'Kullanıcılar',
        'settings' => 'Ayarlar',
        'layout-grid' => 'Izgara',
    ],
];
