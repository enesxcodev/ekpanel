<?php

use Illuminate\Support\Facades\Storage;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Laravel\LaravelFileRepository;

/** @return array<string, mixed> */
function distributionEntry(): array
{
    return [
        'id' => 'sample', 'name' => 'Örnek Modül', 'package' => 'ekagent/sample',
        'description' => 'Test fixture', 'version' => '1.2.3', 'author' => 'Fixture',
        'requires' => ['php' => '^8.4', 'laravel' => '^13.0', 'modules' => []],
        'download' => ['url' => 'https://modules.example.test/sample.zip', 'sha256' => str_repeat('a', 64)],
    ];
}

function prepareDistribution(): void
{
    $disk = Storage::fake('module-distribution');
    $disk->makeDirectory('Modules');
    $disk->put('statuses.json', '{}');
    config([
        'module-distribution.catalog_url' => 'https://modules.example.test/catalog.json',
        'module-distribution.allowed_hosts' => ['modules.example.test'],
        'module-distribution.working_path' => $disk->path('management'),
        'module-distribution.composer_binary' => 'composer',
        'modules.paths.modules' => $disk->path('Modules'),
        'modules.activators.file.statuses-file' => $disk->path('statuses.json'),
    ]);
    app()->forgetInstance(ActivatorInterface::class);
    Module::swap(new LaravelFileRepository(app(), $disk->path('Modules')));
    app()->usePublicPath($disk->path('public'));
}

/** @param array<string, mixed> $changes */
function putDistributionModule(string $name = 'Sample', bool $enabled = true, array $changes = []): void
{
    $disk = Storage::disk('module-distribution');
    $disk->put('Modules/'.$name.'/module.json', json_encode(array_replace([
        'name' => $name, 'alias' => strtolower($name), 'version' => '1.2.3', 'providers' => [],
        'description' => 'Yerel modül', 'requires' => [],
    ], $changes), JSON_THROW_ON_ERROR));
    $disk->put('Modules/'.$name.'/composer.json', json_encode([
        'name' => 'ekagent/'.strtolower($name), 'require' => ['php' => '^8.4', 'laravel/framework' => '^13.0'],
    ], JSON_THROW_ON_ERROR));
    $disk->makeDirectory('Modules/'.$name.'/database/migrations');
    Module::findOrFail($name)->setActive($enabled);
}

/** @param array<string, string> $extraFiles */
function distributionZip(array $extraFiles = []): string
{
    $disk = Storage::disk('module-distribution');
    $zip = new ZipArchive;
    $zip->open($disk->path('fixture.zip'), ZipArchive::CREATE | ZipArchive::OVERWRITE);
    $files = array_replace([
        'module.json' => json_encode([
            'name' => 'Sample', 'alias' => 'sample', 'version' => '1.2.3', 'providers' => [],
            'requires' => [], 'panel' => ['install' => ['seeders' => []]],
        ], JSON_THROW_ON_ERROR),
        'composer.json' => json_encode([
            'name' => 'ekagent/sample', 'require' => ['php' => '^8.4', 'laravel/framework' => '^13.0'],
            'autoload' => ['psr-4' => ['Modules\\Sample\\' => 'app/']],
        ], JSON_THROW_ON_ERROR),
        'features.json' => '{"schema_version":1,"forms":{}}',
        'database/migrations/2026_01_01_000000_fixture.php' => '<?php',
    ], $extraFiles);
    foreach ($files as $name => $bytes) {
        $zip->addFromString($name, $bytes);
    }
    $zip->close();

    return $disk->get('fixture.zip');
}
