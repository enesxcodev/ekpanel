<?php

use App\Services\Admin\Modules\ModuleRelease;
use Illuminate\Support\Facades\Storage;
use Nwidart\Modules\Laravel\Module;

function releaseTestModule(): Module
{
    $disk = Storage::fake('release-test-module');
    $disk->put('Menus/module.json', json_encode([
        'name' => 'Menus', 'version' => '1.2.3', 'providers' => [],
        'panel' => ['assets' => ['routes' => ['admin.menus.*'], 'js' => ['resources/dist/panel.js']]],
    ], JSON_THROW_ON_ERROR));
    $disk->put('Menus/composer.json', '{"name":"ekagent/menus","require":{"php":"^8.4","laravel/framework":"^13.0"}}');
    $disk->put('Menus/features.json', '{"schema_version":1,"forms":{}}');
    $disk->put('Menus/resources/dist/panel.js', 'export default function () {}');
    $disk->put('Menus/tests/Feature/MenuWorkspaceTest.php', '<?php');

    return new Module(app(), 'Menus', $disk->path('Menus'));
}

test('release ZIP contains root manifests compiled assets and module tests but excludes local artifacts', function () {
    $module = releaseTestModule();
    $disk = Storage::disk('release-test-module');
    foreach (['backups/old.php', '.env', 'vendor/autoload.php', 'resources/assets/panel.js.bak', 'database/customer.sqlite'] as $path) {
        $disk->put('Menus/'.$path, 'private');
    }

    $release = app(ModuleRelease::class)->package($module, $disk->path('releases'));

    $zip = new ZipArchive;
    expect($zip->open($release['path']))->toBeTrue();
    try {
        expect($zip->getFromName('module.json'))->toContain('"version":"1.2.3"');
        expect($zip->getFromName('composer.json'))->toContain('ekagent/menus');
        expect($zip->getFromName('resources/dist/panel.js'))->toBe('export default function () {}');
        expect($zip->getFromName('tests/Feature/MenuWorkspaceTest.php'))->toBe('<?php');
        expect($zip->numFiles)->toBe(5);
    } finally {
        $zip->close();
    }
    expect($release['sha256'])->toBe(hash_file('sha256', $release['path']));
});

test('packaging refuses to overwrite a previously created version', function () {
    $module = releaseTestModule();
    $directory = Storage::disk('release-test-module')->path('releases');
    $service = app(ModuleRelease::class);
    $release = $service->package($module, $directory);

    expect(fn () => $service->package($module, $directory))->toThrow(RuntimeException::class, 'Sürüm paketi zaten var');

    expect(hash_file('sha256', $release['path']))->toBe($release['sha256']);
});

test('packaging refuses missing compiled assets before creating a release', function () {
    $module = releaseTestModule();
    $disk = Storage::disk('release-test-module');
    $disk->delete('Menus/resources/dist/panel.js');

    expect(fn () => app(ModuleRelease::class)->package($module, $disk->path('releases')))->toThrow(RuntimeException::class);

    $disk->assertMissing('releases/Menus-v1.2.3.zip');
});

test('catalog metadata is derived from release manifests and actual ZIP bytes', function () {
    $module = releaseTestModule();
    config(['module-distribution.allowed_hosts' => ['modules.example.test']]);
    $service = app(ModuleRelease::class);
    $release = $service->package($module, Storage::disk('release-test-module')->path('releases'));

    $entry = $service->catalogEntry($module, $release, 'https://modules.example.test/Menus-v1.2.3.zip');

    expect($entry)->toMatchArray([
        'id' => 'menus', 'package' => 'ekagent/menus', 'version' => '1.2.3', 'author' => 'ekagent',
        'download' => ['url' => 'https://modules.example.test/Menus-v1.2.3.zip', 'sha256' => $release['sha256']],
    ]);
    expect($entry['requires']['php'])->toBe('^8.4');
    expect($entry['requires']['laravel'])->toBe('^13.0');
    expect($entry)->not->toHaveKey('panel');
});

test('a published ZIP can generate catalog metadata later without changing its hash', function () {
    $module = releaseTestModule();
    $service = app(ModuleRelease::class);
    $release = $service->package($module, Storage::disk('release-test-module')->path('releases'));

    expect($service->existing($module, $release['path']))->toBe($release);

    Storage::disk('release-test-module')->put('Menus/composer.json', '{"name":"ekagent/changed"}');
    expect(fn () => $service->existing($module, $release['path']))->toThrow(RuntimeException::class, 'metadata ile eşleşmiyor');
});
