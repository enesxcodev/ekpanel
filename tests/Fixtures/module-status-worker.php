<?php

use App\Services\Admin\Modules\LockedFileActivator;
use Illuminate\Config\Repository;
use Illuminate\Container\Container;

require dirname(__DIR__, 2).'/vendor/autoload.php';

$container = new Container;
$container['config'] = new Repository(['modules' => ['activators' => ['file' => ['statuses-file' => $argv[1]]]]]);
$activator = new LockedFileActivator($container);

for ($iteration = 0; $iteration < 200; $iteration++) {
    if ($argv[2] === 'reader') {
        if (! $activator->hasStatus('Existing', true)) {
            throw new RuntimeException('A concurrent reader observed a missing activation state.');
        }
    } else {
        $activator->setActiveByName($argv[2], true);
    }
}
