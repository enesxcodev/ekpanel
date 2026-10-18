<?php

use App\Services\Admin\Modules\ModulePanelAssets;
use Illuminate\Support\Facades\Storage;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Laravel\LaravelFileRepository;

function registerAssetTestModule(bool $enabled = true, string $path = 'resources/dist/panel.js'): void
{
    $disk = Storage::fake('asset-test-modules');
    $disk->put('Menus/module.json', json_encode([
        'name' => 'Menus', 'version' => '1.2.3', 'providers' => [],
        'panel' => ['assets' => ['routes' => ['admin.menus.*'], 'js' => [$path], 'css' => ['resources/dist/panel.css']]],
    ], JSON_THROW_ON_ERROR));
    $disk->put('Menus/resources/dist/panel.js', 'export default function () {}');
    $disk->put('Menus/resources/dist/panel.css', '.menu { color: red; }');
    $disk->put('statuses.json', json_encode(['Menus' => $enabled], JSON_THROW_ON_ERROR));
    config(['modules.activators.file.statuses-file' => $disk->path('statuses.json')]);
    app()->forgetInstance(ActivatorInterface::class);
    Module::swap(new LaravelFileRepository(app(), $disk->path('')));
    app()->usePublicPath($disk->path('public'));
}

test('published module assets load only for enabled modules on matching routes', function (bool $enabled, ?string $route, bool $visible) {
    registerAssetTestModule($enabled);
    $this->artisan('module:panel-assets', ['module' => 'Menus'])->assertSuccessful();

    $assets = app(ModulePanelAssets::class)->forRoute($route);

    expect($assets['js'])->toHaveCount($visible ? 1 : 0);
    expect($assets['css'])->toHaveCount($visible ? 1 : 0);
    if ($visible) {
        expect(file_get_contents(public_path(parse_url($assets['js'][0], PHP_URL_PATH))))->toBe('export default function () {}');
        expect($assets['js'][0])->toContain('/modules/menus/1.2.3-');
    }
})->with([
    'matching route' => [true, 'admin.menus.index', true],
    'another module' => [true, 'admin.pages.index', false],
    'disabled module' => [false, 'admin.menus.index', false],
    'unnamed route' => [true, null, false],
]);

test('changing compiled content gives a new asset URL without overwriting the old release', function () {
    registerAssetTestModule();
    $assets = app(ModulePanelAssets::class);
    $module = Module::findOrFail('Menus');
    $assets->publish($module);
    $previous = $assets->forRoute('admin.menus.index')['js'][0];
    Storage::disk('asset-test-modules')->put('Menus/resources/dist/panel.js', 'export default function () { return 2; }');

    $assets->publish($module);

    expect($assets->forRoute('admin.menus.index')['js'][0])->not->toBe($previous);
    expect(file_get_contents(public_path(parse_url($previous, PHP_URL_PATH))))->toBe('export default function () {}');
});

test('asset publication rejects unsafe paths before writing public files', function (string $path) {
    registerAssetTestModule(path: $path);

    $this->artisan('module:panel-assets', ['module' => 'Menus'])->assertFailed();

    expect(is_dir(public_path('modules')))->toBeFalse();
})->with(['../outside.js', 'resources/dist/../../outside.js', '/tmp/panel.js', 'https://example.com/panel.js', 'resources/dist/payload.php', 'resources\\dist\\panel.js']);

test('missing compiled assets fail publication instead of emitting a broken URL', function () {
    registerAssetTestModule();
    Storage::disk('asset-test-modules')->delete('Menus/resources/dist/panel.js');

    $this->artisan('module:panel-assets', ['module' => 'Menus'])->assertFailed();

    expect(is_dir(public_path('modules')))->toBeFalse();
});

test('matching screens report an unpublished release', function () {
    registerAssetTestModule();

    expect(fn () => app(ModulePanelAssets::class)->forRoute('admin.menus.index'))
        ->toThrow(RuntimeException::class, 'Modül assetleri yayınlanmamış');
});
