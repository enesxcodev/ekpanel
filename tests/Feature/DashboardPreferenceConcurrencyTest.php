<?php

use App\Models\Admin\Admin;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Process\Process;

uses(LazilyRefreshDatabase::class);

test('concurrent first saves create one preference and return a conflict to the second writer', function () {
    $disk = Storage::fake('dashboard-preference-race');
    $database = $disk->path('race.sqlite');
    touch($database);
    config(['database.connections.widget-race' => [...config('database.connections.sqlite'), 'database' => $database, 'busy_timeout' => 5000]]);
    $this->artisan('migrate', [
        '--database' => 'widget-race',
        '--path' => ['database/migrations/2026_10_15_142450_create_admins_table.php', 'database/migrations/2026_10_22_172810_create_admin_dashboard_preferences_table.php'],
        '--force' => true,
    ])->assertSuccessful();
    Admin::factory()->connection('widget-race')->create(['id' => 1]);
    $gate = $disk->path('start');
    $command = [PHP_BINARY, base_path('tests/Fixtures/dashboard-preference-worker.php'), $database, $gate];
    $first = new Process($command, base_path());
    $second = new Process($command, base_path());
    $first->setTimeout(20)->start();
    $second->setTimeout(20)->start();
    try {
        expect($first->waitUntil(fn (string $type, string $output): bool => str_contains($output, 'ready')))->toBeTrue();
        expect($second->waitUntil(fn (string $type, string $output): bool => str_contains($output, 'ready')))->toBeTrue();

        $disk->put('start', 'go');
        $first->wait();
        $second->wait();

        expect($first->isSuccessful(), $first->getErrorOutput())->toBeTrue();
        expect($second->isSuccessful(), $second->getErrorOutput())->toBeTrue();
        $output = $first->getOutput().$second->getOutput();
        expect(substr_count($output, 'saved'))->toBe(1);
        expect(substr_count($output, 'conflict'))->toBe(1);
        expect(DB::connection('widget-race')->table('admin_dashboard_preferences')->count())->toBe(1);
        expect((int) DB::connection('widget-race')->table('admin_dashboard_preferences')->value('revision'))->toBe(1);
    } finally {
        $first->stop();
        $second->stop();
        DB::disconnect('widget-race');
    }
});
