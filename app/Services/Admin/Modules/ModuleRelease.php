<?php

namespace App\Services\Admin\Modules;

use Illuminate\Filesystem\Filesystem;
use Nwidart\Modules\Module;
use RuntimeException;
use Symfony\Component\Finder\Finder;
use ZipArchive;

class ModuleRelease
{
    public function __construct(private ModulePanelAssets $assets) {}

    /** @return array{path: string, sha256: string, version: string} */
    public function existing(Module $module, string $path): array
    {
        $zip = new ZipArchive;
        if ($zip->open($path, ZipArchive::CHECKCONS) !== true) {
            throw new RuntimeException('Mevcut sürüm ZIP dosyası geçersiz.');
        }
        try {
            foreach (['module.json', 'composer.json'] as $manifest) {
                if ($zip->getFromName($manifest) !== file_get_contents($module->getPath().'/'.$manifest)) {
                    throw new RuntimeException('Mevcut ZIP metadata ile eşleşmiyor; yeni sürüm paketleyin.');
                }
            }
        } finally {
            $zip->close();
        }

        return ['path' => $path, 'sha256' => hash_file('sha256', $path), 'version' => $module->get('version')];
    }

    /**
     * @param  array{path: string, sha256: string, version: string}  $release
     * @return array<string, mixed>
     */
    public function catalogEntry(Module $module, array $release, string $url): array
    {
        app(ModuleDownload::class)->assertAllowed($url);
        $composer = json_decode(file_get_contents($module->getPath().'/composer.json'), true, 64, JSON_THROW_ON_ERROR);

        return [
            'id' => $module->get('alias', strtolower($module->getName())),
            'name' => $module->getName(),
            'package' => $composer['name'],
            'description' => $module->get('description', ''),
            'version' => $release['version'],
            'author' => implode(', ', array_column($composer['authors'] ?? [], 'name')) ?: explode('/', $composer['name'])[0],
            'requires' => [
                'php' => $composer['require']['php'],
                'laravel' => $composer['require']['laravel/framework'],
                'modules' => (object) $module->get('requires', []),
            ],
            'download' => ['url' => $url, 'sha256' => $release['sha256']],
        ];
    }

    /** @return array{path: string, sha256: string, version: string} */
    public function package(Module $module, string $directory): array
    {
        $name = $module->getName();
        $version = $module->get('version');
        if (! preg_match('/^[A-Za-z][A-Za-z0-9]*$/D', $name) || ! is_string($version) || ! preg_match('/^\d+\.\d+\.\d+(?:-[a-zA-Z0-9.-]+)?$/D', $version)) {
            throw new RuntimeException('Paket için geçerli modül adı ve sürümü gereklidir.');
        }
        foreach (['module.json', 'composer.json', 'features.json'] as $required) {
            if (! is_file($module->getPath().'/'.$required)) {
                throw new RuntimeException('Eksik modül dosyası: '.$required);
            }
            json_decode(file_get_contents($module->getPath().'/'.$required), true, 64, JSON_THROW_ON_ERROR);
        }
        $this->assets->files($module);
        $filesystem = new Filesystem;
        $filesystem->ensureDirectoryExists($directory);
        $destination = $directory.'/'.$name.'-v'.$version.'.zip';
        if (file_exists($destination)) {
            throw new RuntimeException('Sürüm paketi zaten var; yeni sürüm kullanın.');
        }
        $temporary = tempnam($directory, '.module-');
        if ($temporary === false) {
            throw new RuntimeException('Geçici paket dosyası oluşturulamadı.');
        }
        $zip = new ZipArchive;
        $opened = false;
        try {
            if ($zip->open($temporary, ZipArchive::OVERWRITE) !== true) {
                throw new RuntimeException('ZIP oluşturulamadı.');
            }
            $opened = true;
            $finder = Finder::create()->files()->in($module->getPath())->ignoreDotFiles(false)
                ->exclude(['backups', 'node_modules', 'vendor', '.git', '.idea', '.vscode'])
                ->notName(['*.bak', '*.log', '.env*', '*.zip', '*.sqlite', '*.map'])->sortByName();
            foreach ($finder as $file) {
                $relative = str_replace('\\', '/', $file->getRelativePathname());
                $first = explode('/', $relative)[0];
                if (! in_array($first, ['app', 'config', 'database', 'resources', 'routes', 'tests', 'skills', '.ai'], true)
                    && ! in_array($relative, ['composer.json', 'module.json', 'features.json', 'vite.config.js'], true)) {
                    continue;
                }
                if ($file->isLink() || ! str_starts_with($file->getRealPath(), realpath($module->getPath()).DIRECTORY_SEPARATOR)) {
                    throw new RuntimeException('Paket sembolik bağlantı içeremez: '.$relative);
                }
                if (! $zip->addFile($file->getPathname(), $relative)) {
                    throw new RuntimeException('Dosya pakete eklenemedi: '.$relative);
                }
            }
            if (! $zip->close()) {
                throw new RuntimeException('ZIP yazılamadı.');
            }
            $opened = false;
            if (! rename($temporary, $destination)) {
                throw new RuntimeException('Paket kaydedilemedi.');
            }
        } finally {
            if ($opened) {
                $zip->close();
            }
            if (file_exists($temporary)) {
                @unlink($temporary);
            }
        }

        return ['path' => $destination, 'sha256' => hash_file('sha256', $destination), 'version' => $version];
    }
}
