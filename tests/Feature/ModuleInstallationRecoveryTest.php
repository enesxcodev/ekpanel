<?php

use App\Services\Admin\Modules\ModuleManager;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Storage;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Facades\Module;

require_once __DIR__.'/../Fixtures/module-distribution.php';

test('a fresh install enables the module only after installation succeeds', function () {
    prepareDistribution();
    $bytes = distributionZip();
    $entry = distributionEntry();
    $entry['download']['sha256'] = hash('sha256', $bytes);
    Http::preventStrayRequests();
    Http::fake([$entry['download']['url'] => Http::response($bytes)]);
    Process::preventStrayProcesses();
    Process::fake();

    app(ModuleManager::class)->install($entry, 7);

    expect(Module::findOrFail('Sample')->isEnabled())->toBeTrue();
    Storage::disk('module-distribution')->assertMissing('management/pending-Sample.json');
    expect(app(ModuleManager::class)->history()[0]['status'])->toBe('success');
    Http::assertSentCount(1);
    Process::assertRan(fn ($process): bool => in_array('module:permissions', $process->command, true));
});

test('a stale enabled status is disabled before installation commands expose a new module', function () {
    prepareDistribution();
    $disk = Storage::disk('module-distribution');
    $disk->put('statuses.json', '{"Sample":true}');
    $bytes = distributionZip();
    $entry = distributionEntry();
    $entry['download']['sha256'] = hash('sha256', $bytes);
    Http::preventStrayRequests();
    Http::fake([$entry['download']['url'] => Http::response($bytes)]);
    Process::preventStrayProcesses();
    Process::fake(fn () => Process::result(exitCode: 1));

    expect(fn () => app(ModuleManager::class)->install($entry, 7))
        ->toThrow(RuntimeException::class, 'devre dışı tutuldu');

    expect(json_decode($disk->get('statuses.json'), true)['Sample'])->toBeFalse();
    expect(Module::findOrFail('Sample')->isEnabled())->toBeFalse();
    $disk->assertExists('management/pending-Sample.json');
    expect(app(ModuleManager::class)->history()[0]['status'])->toBe('failed');
    Http::assertSentCount(1);
    Process::assertRanTimes(fn ($process): bool => in_array('update', $process->command, true), 1);
});

test('a status write failure leaves the new module outside discovery even with a stale enabled status', function () {
    prepareDistribution();
    $disk = Storage::disk('module-distribution');
    $disk->put('statuses.json', '{"Sample":true}');
    app(ActivatorInterface::class);
    $disk->delete('statuses.json');
    $disk->makeDirectory('statuses.json');
    $bytes = distributionZip();
    $entry = distributionEntry();
    $entry['download']['sha256'] = hash('sha256', $bytes);
    Http::preventStrayRequests();
    Http::fake([$entry['download']['url'] => Http::response($bytes)]);
    Process::preventStrayProcesses();

    expect(fn () => app(ModuleManager::class)->install($entry, 7))->toThrow(RuntimeException::class);

    $disk->assertMissing('Modules/Sample');
    expect(Module::find('Sample'))->toBeNull();
    expect(app(ModuleManager::class)->history()[0]['status'])->toBe('failed');
    Http::assertSentCount(1);
    Process::assertNothingRan();
});
