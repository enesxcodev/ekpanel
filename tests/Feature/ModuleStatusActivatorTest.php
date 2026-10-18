<?php

use Illuminate\Support\Facades\Storage;
use Nwidart\Modules\Laravel\Module;
use Symfony\Component\Process\Process;

test('independent activator instances merge status changes instead of overwriting other modules', function () {
    $disk = Storage::fake('module-statuses');
    $disk->put('statuses.json', '{"Existing":true}');
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    $class = config('modules.activators.file.class');
    $first = new $class(app());
    $second = new $class(app());

    $first->setActiveByName('First', true);
    $second->setActiveByName('Second', true);

    expect(json_decode($disk->get('statuses.json'), true))->toBe(['Existing' => true, 'First' => true, 'Second' => true]);
    expect($first->hasStatus('Second', true))->toBeTrue();
});

test('missing statuses and enable disable delete and reset retain the module activation contract', function () {
    $disk = Storage::fake('module-statuses');
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    $class = config('modules.activators.file.class');
    $activator = new $class(app());
    $module = new Module(app(), 'First', $disk->path('First'));
    expect($activator->hasStatus($module, false))->toBeTrue();

    $activator->enable($module);
    expect($activator->hasStatus($module, true))->toBeTrue();
    $activator->disable($module);
    expect($activator->hasStatus($module, false))->toBeTrue();
    $activator->setActiveByName('Other', true);
    $activator->delete($module);
    expect(json_decode($disk->get('statuses.json'), true))->toBe(['Other' => true]);
    $activator->reset();
    expect(json_decode($disk->get('statuses.json'), true))->toBe([]);
    expect($activator->hasStatus('Other', false))->toBeTrue();
});

test('transient Windows file handles cannot break status writes or discard existing activation states', function (string $mode) {
    $disk = Storage::fake('module-statuses');
    $disk->put('statuses.json', '{"Existing":true}');
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    $class = config('modules.activators.file.class');
    $activator = new $class(app());
    $process = new Process(['powershell.exe', '-NoProfile', '-NonInteractive', '-File', base_path('tests/Fixtures/module-status-handle.ps1'),
        '-StatusPath', $disk->path('statuses.json'), '-ReadyPath', $disk->path('ready'), '-Mode', $mode]);
    $process->start();
    $deadline = microtime(true) + 5;
    try {
        while (! is_file($disk->path('ready')) && microtime(true) < $deadline && $process->isRunning()) {
            usleep(10000);
        }
        expect(is_file($disk->path('ready')))->toBeTrue($process->getErrorOutput());

        $activator->setActiveByName('Installed', true);

        expect(json_decode($disk->get('statuses.json'), true))->toBe(['Existing' => true, 'Installed' => true]);
    } finally {
        $process->wait();
    }
})->with(['reader', 'sharing', 'mapping'])->skip(PHP_OS_FAMILY !== 'Windows');

test('parallel readers and writers preserve valid complete status data and every modules final state', function () {
    $disk = Storage::fake('module-statuses');
    $disk->put('statuses.json', '{"Existing":true}');
    $processes = [];
    foreach (['First', 'Second', 'Third', 'reader', 'reader'] as $name) {
        $process = new Process([PHP_BINARY, base_path('tests/Fixtures/module-status-worker.php'), $disk->path('statuses.json'), $name]);
        $process->setTimeout(30)->start();
        $processes[] = $process;
    }

    foreach ($processes as $process) {
        expect($process->wait())->toBe(0, $process->getErrorOutput());
    }

    expect(json_decode($disk->get('statuses.json'), true))->toHaveCount(4)->toMatchArray([
        'Existing' => true, 'First' => true, 'Second' => true, 'Third' => true,
    ]);
});

test('invalid existing status data is rejected without replacing it or guessing inactive modules', function (string $content) {
    $disk = Storage::fake('module-statuses');
    $disk->put('statuses.json', $content);
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    $class = config('modules.activators.file.class');
    $activator = new $class(app());

    expect(fn () => $activator->setActiveByName('Installed', true))->toThrow($content === '{broken' ? JsonException::class : RuntimeException::class);

    expect($disk->get('statuses.json'))->toBe($content);
})->with(['{broken', '{"Existing":"yes"}']);

test('a persistent Windows sharing failure is reported and retains the complete previous file', function () {
    $disk = Storage::fake('module-statuses');
    $disk->put('statuses.json', '{"Existing":true}');
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    $class = config('modules.activators.file.class');
    $activator = new $class(app());
    $process = new Process(['powershell.exe', '-NoProfile', '-NonInteractive', '-File', base_path('tests/Fixtures/module-status-handle.ps1'),
        '-StatusPath', $disk->path('statuses.json'), '-ReadyPath', $disk->path('ready'), '-Mode', 'sharing', '-ReleasePath', $disk->path('release')]);
    $process->start();
    try {
        $deadline = microtime(true) + 5;
        while (! is_file($disk->path('ready')) && microtime(true) < $deadline && $process->isRunning()) {
            usleep(10000);
        }
        expect(is_file($disk->path('ready')))->toBeTrue($process->getErrorOutput());

        expect(fn () => $activator->setActiveByName('Installed', true))->toThrow(RuntimeException::class, 'önceki dosya korunuyor');

        expect($disk->get('statuses.json'))->toBe('{"Existing":true}');
        expect(glob($disk->path('.module-status-*.tmp')))->toBe([]);
    } finally {
        $disk->put('release', 'release');
        $process->wait();
    }
})->skip(PHP_OS_FAMILY !== 'Windows');
