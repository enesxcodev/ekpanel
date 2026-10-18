<?php

namespace App\Services\Admin\Modules;

use Illuminate\Filesystem\Filesystem;
use RuntimeException;
use ZipArchive;

class ModuleArchive
{
    /** @param array<string, mixed> $catalog
     * @return array{module: array<string, mixed>, composer: array<string, mixed>}
     */
    public function extract(string $archive, string $destination, array $catalog): array
    {
        $zip = new ZipArchive;
        if ($zip->open($archive, ZipArchive::CHECKCONS) !== true) {
            throw new RuntimeException('Modül ZIP arşivi geçersiz.');
        }
        try {
            if ($zip->numFiles > config('module-distribution.max_files')) {
                throw new RuntimeException('ZIP dosya sayısı sınırı aşıldı.');
            }
            $total = 0;
            $seen = [];
            $entries = [];
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $stat = $zip->statIndex($index);
                $path = $stat['name'];
                $segments = explode('/', rtrim($path, '/'));
                foreach ($segments as $segment) {
                    if ($segment === '' || $segment === '.' || $segment === '..'
                        || preg_match('/[\\\\:\x00-\x1f]/', $segment) || preg_match('/[. ]$/D', $segment)
                        || preg_match('/^(CON|PRN|AUX|NUL|COM[1-9]|LPT[1-9])(?:\\.|$)/i', $segment)) {
                        throw new RuntimeException('Güvensiz ZIP dosya yolu: '.$path);
                    }
                }
                $key = strtolower(rtrim($path, '/'));
                if (isset($seen[$key])) {
                    throw new RuntimeException('ZIP yinelenen dosya içeriyor.');
                }
                $seen[$key] = true;
                $zip->getExternalAttributesIndex($index, $system, $attributes);
                $type = ($attributes >> 16) & 0170000;
                if (! in_array($type, [0, 0100000, 0040000], true) || ($stat['encryption_method'] ?? 0) !== 0) {
                    throw new RuntimeException('ZIP bağlantı, özel veya şifreli dosya içeremez.');
                }
                $total += $stat['size'];
                if ($total > config('module-distribution.max_unpacked_bytes')) {
                    throw new RuntimeException('ZIP açılmış boyut sınırı aşıldı.');
                }
                $entries[] = $path;
            }
            $filesystem = new Filesystem;
            $filesystem->ensureDirectoryExists($destination);
            $written = 0;
            foreach ($entries as $path) {
                $target = $destination.'/'.$path;
                if (str_ends_with($path, '/')) {
                    $filesystem->ensureDirectoryExists($target);

                    continue;
                }
                $filesystem->ensureDirectoryExists(dirname($target));
                $input = $zip->getStream($path);
                $output = fopen($target, 'xb');
                if ($input === false || $output === false) {
                    throw new RuntimeException('ZIP dosyası açılamadı.');
                }
                try {
                    while (! feof($input)) {
                        $chunk = fread($input, 65536);
                        if ($chunk === false) {
                            throw new RuntimeException('ZIP dosyası okunamadı.');
                        }
                        $written += strlen($chunk);
                        if ($written > config('module-distribution.max_unpacked_bytes') || fwrite($output, $chunk) !== strlen($chunk)) {
                            throw new RuntimeException('ZIP açılırken boyut veya disk sınırı aşıldı.');
                        }
                    }
                } finally {
                    fclose($input);
                    fclose($output);
                }
            }
        } finally {
            $zip->close();
        }
        foreach (['module.json', 'composer.json', 'features.json'] as $file) {
            if (! is_file($destination.'/'.$file)) {
                throw new RuntimeException('ZIP kökünde gerekli dosya yok: '.$file);
            }
        }
        $module = json_decode(file_get_contents($destination.'/module.json'), true, 64, JSON_THROW_ON_ERROR);
        $composer = json_decode(file_get_contents($destination.'/composer.json'), true, 64, JSON_THROW_ON_ERROR);
        $features = json_decode(file_get_contents($destination.'/features.json'), true, 64, JSON_THROW_ON_ERROR);
        if (! is_array($features) || ($features['schema_version'] ?? null) !== 1 || ! is_array($features['forms'] ?? null)) {
            throw new RuntimeException('Modül alan şeması geçersiz.');
        }
        if (! is_array($module) || ! is_array($composer)
            || ! preg_match('/^[A-Z][A-Za-z0-9]*$/D', $module['name'] ?? '')
            || strtolower($module['name']) !== $catalog['id']
            || ($module['alias'] ?? '') !== $catalog['id']
            || ($module['version'] ?? '') !== $catalog['version']
            || ($composer['name'] ?? '') !== $catalog['package']
            || ($composer['require']['php'] ?? '') !== $catalog['requires']['php']
            || ($composer['require']['laravel/framework'] ?? '') !== $catalog['requires']['laravel']
            || ($module['requires'] ?? []) !== $catalog['requires']['modules']) {
            throw new RuntimeException('ZIP kimliği, sürümü veya bağımlılıkları katalogla eşleşmiyor.');
        }
        foreach ($composer['autoload']['psr-4'] ?? [] as $namespace => $paths) {
            if (! str_starts_with($namespace, 'Modules'.chr(92).$module['name'].chr(92))) {
                throw new RuntimeException('Modül autoload namespace geçersiz.');
            }
            foreach ((array) $paths as $path) {
                if (! is_string($path) || preg_match('~(^/|\\\\|:|(?:^|/)\\.\\.(?:/|$))~', $path)) {
                    throw new RuntimeException('Modül autoload yolu geçersiz.');
                }
            }
        }
        if (array_diff(array_keys($composer['autoload'] ?? []), ['psr-4', 'exclude-from-classmap']) !== []) {
            throw new RuntimeException('Modül dağıtımı yalnız PSR-4 autoload destekler.');
        }
        foreach ($composer['require'] ?? [] as $package => $constraint) {
            if (! is_string($package) || ! preg_match('~^(?:php(?:-64bit|-ipv6)?|ext-[a-z0-9_-]+|lib-[a-z0-9_-]+|composer(?:-plugin-api|-runtime-api)?|[a-z0-9_.-]+/[a-z0-9_.-]+)$~D', $package)
                || ! is_string($constraint)) {
                throw new RuntimeException('Composer paket bağımlılığı geçersiz.');
            }
        }

        return compact('module', 'composer');
    }
}
