<?php

use Modules\Pages\Services\PageCategoryMenuSource;
use Modules\Pages\Services\PageMenuSource;

return [
    'name' => 'Pages',
    'navigation_sources' => [
        'pages' => PageMenuSource::class,
        'page_categories' => PageCategoryMenuSource::class,
    ],
];
