<?php

return [
    'catalog_url' => env('MODULE_CATALOG_URL'),
    'allowed_hosts' => array_filter(explode(',', env('MODULE_DOWNLOAD_HOSTS', 'raw.githubusercontent.com,github.com,api.github.com,objects.githubusercontent.com,release-assets.githubusercontent.com'))),
    'max_download_bytes' => 52428800,
    'max_unpacked_bytes' => 209715200,
    'max_files' => 5000,
    'composer_binary' => env('MODULE_COMPOSER_BINARY', 'composer'),
];
