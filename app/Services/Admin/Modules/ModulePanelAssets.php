<?php

namespace App\Services\Admin\Modules;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Module as ModuleInstance;
use RuntimeException;

class ModulePanelAssets
{
    /** @return array{js: list<string>, css: list<string>} */
    public function forRoute(?string $route): array
    {
        $assets = ['js' => [], 'css' => []];
        if ($route === null) {
            return $assets;
        }

        foreach (Module::allEnabled() as $module) {
            $definition = $module->get('panel', [])['assets'] ?? [];
            if (! Str::is($definition['routes'] ?? [], $route)) {
                continue;
            }

            $files = $this->files($module);
            $prefix = $this->prefix($module, $files);
            foreach ($files as $file) {
                $relative = $prefix.'/'.$file['path'];
                if (! is_file(public_path($relative))) {
                    throw new RuntimeException('Modül assetleri yayınlanmamış: '.$module->getName().'. module:panel-assets komutunu çalıştırın.');
                }
                $assets[$file['type']][] = asset($relative);
            }
        }

        return $assets;
    }

    /** @return list<array{path: string, source: string, type: string, hash: string}> */
    public function files(ModuleInstance $module): array
    {
        $files = [];
        $definition = $module->get('panel', [])['assets'] ?? [];
        foreach (['js', 'css'] as $type) {
            foreach ($definition[$type] ?? [] as $path) {
                if (! is_string($path) || ! preg_match('~^resources/dist/(?:[a-zA-Z0-9_-]+/)*[a-zA-Z0-9_.-]+\.'.$type.'$~D', $path)) {
                    throw new RuntimeException('Geçersiz modül asset yolu.');
                }
                $root = realpath($module->getPath());
                $source = realpath($module->getPath().'/'.$path);
                if ($root === false || $source === false || ! is_file($source) || ! str_starts_with($source, $root.DIRECTORY_SEPARATOR)) {
                    throw new RuntimeException('Modül asset dosyası bulunamadı veya modül dışında: '.$path);
                }
                $files[] = ['path' => $path, 'source' => $source, 'type' => $type, 'hash' => hash_file('sha256', $source)];
            }
        }

        return $files;
    }

    /** @param list<array{path: string, source: string, type: string, hash: string}> $files */
    private function prefix(ModuleInstance $module, array $files): string
    {
        $name = $module->getName();
        $version = $module->get('version');
        if (! preg_match('/^[A-Za-z][A-Za-z0-9]*$/D', $name) || ! is_string($version) || ! preg_match('/^\d+\.\d+\.\d+(?:-[a-zA-Z0-9.-]+)?$/D', $version)) {
            throw new RuntimeException('Modül adı veya sürümü geçersiz.');
        }
        $hash = hash('sha256', json_encode(array_map(fn (array $file): array => [$file['path'], $file['hash']], $files), JSON_THROW_ON_ERROR));

        return 'modules/'.strtolower($name).'/'.$version.'-'.substr($hash, 0, 16);
    }

    public function publish(ModuleInstance $module): int
    {
        $files = $this->files($module);
        if ($files === []) {
            return 0;
        }
        $prefix = $this->prefix($module, $files);
        $filesystem = new Filesystem;
        foreach ($files as $file) {
            $destination = public_path($prefix.'/'.$file['path']);
            $filesystem->ensureDirectoryExists(dirname($destination));
            if (! $filesystem->copy($file['source'], $destination)) {
                throw new RuntimeException('Modül asset dosyası yayınlanamadı: '.$file['path']);
            }
        }

        return count($files);
    }
}
