<?php

use App\Models\Admin\Admin;
use App\Services\Admin\Dashboard\DashboardPreferenceService;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

require dirname(__DIR__, 2).'/vendor/autoload.php';
$app = require dirname(__DIR__, 2).'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();
config(['database.default' => 'sqlite', 'database.connections.sqlite.database' => $argv[1], 'database.connections.sqlite.busy_timeout' => 5000]);
DB::purge('sqlite');
$admin = new Admin;
$admin->id = 1;
echo "ready\n";
flush();
$deadline = microtime(true) + 10;
while (! file_exists($argv[2])) {
    if (microtime(true) >= $deadline) {
        exit(2);
    }
    usleep(1000);
    clearstatcache(true, $argv[2]);
}
try {
    app(DashboardPreferenceService::class)->save($admin, ['widgetfixture.total'], 0, ['widgetfixture.total']);
    echo "saved\n";
} catch (HttpExceptionInterface $exception) {
    if ($exception->getStatusCode() !== 409) {
        throw $exception;
    }
    echo "conflict\n";
}
