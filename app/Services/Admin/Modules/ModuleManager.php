<?php

namespace App\Services\Admin\Modules;

use Closure;
use Composer\InstalledVersions;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Str;
use Nwidart\Modules\Facades\Module;
use Nwidart\Modules\Laravel\Module as ModuleInstance;
use RuntimeException;
use Throwable;

class ModuleManager
{
    public function __construct(
        private ModuleDownload $download,
        private ModuleArchive $archive,
        private ModuleCompatibility $compatibility,
        private ModulePanelAssets $assets,
    ) {}

    public function workingPath(): string
    {
        return config('module-distribution.working_path', storage_path('app/module-management'));
    }

    /** @return list<array<string, mixed>> */
    public function history(): array
    {
        $path = $this->workingPath().'/operations.jsonl';
        if (! is_file($path)) {
            return [];
        }

        return array_reverse(array_slice(array_map(
            fn (string $line): array => json_decode($line, true, 64, JSON_THROW_ON_ERROR),
            file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES),
        ), -20));
    }

    private function run(string $action, string $name, int $actor, callable $operation): void
    {
        $filesystem = new Filesystem;
        $filesystem->ensureDirectoryExists($this->workingPath());
        $handle = fopen($this->workingPath().'/management.lock', 'c');
        if ($handle === false || ! flock($handle, LOCK_EX | LOCK_NB)) {
            if (is_resource($handle)) {
                fclose($handle);
            }
            throw new RuntimeException('Başka bir modül işlemi sürüyor. Tamamlanınca yeniden deneyin.');
        }
        $record = ['id' => (string) Str::uuid(), 'action' => $action, 'module' => $name, 'actor' => $actor, 'at' => now()->toIso8601String()];
        try {
            $operation();
            $record['status'] = 'success';
        } catch (Throwable $exception) {
            $record['status'] = 'failed';
            $record['error'] = $exception->getMessage();
            throw $exception;
        } finally {
            file_put_contents($this->workingPath().'/operations.jsonl', json_encode($record, JSON_THROW_ON_ERROR).PHP_EOL, FILE_APPEND | LOCK_EX);
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /** @param list<string> $arguments */
    private function command(array $arguments): void
    {
        $result = Process::path(base_path())->timeout(300)->run($arguments);
        if (! $result->successful()) {
            throw new RuntimeException('Modül komutu başarısız: '.implode(' ', array_slice($arguments, 0, 3)).' (çıkış '.$result->exitCode().').');
        }
    }

    /** @param list<string> $arguments */
    private function artisan(array $arguments): void
    {
        $this->command([PHP_BINARY, base_path('artisan'), ...$arguments, '--no-interaction']);
    }

    private function root(): string
    {
        $root = realpath(config('modules.paths.modules'));
        if ($root === false) {
            throw new RuntimeException('Modül klasörü bulunamadı.');
        }

        return $root;
    }

    private function assertLocal(ModuleInstance $module): void
    {
        if (realpath($module->getPath()) !== $this->root().DIRECTORY_SEPARATOR.$module->getName()
            || is_link($module->getPath())) {
            throw new RuntimeException('Modül yolu yönetilen klasörün doğrudan altında olmalıdır.');
        }
    }

    private function assertNoDependents(ModuleInstance $module, bool $removing): void
    {
        foreach (Module::all() as $other) {
            if ($other->getName() === $module->getName() || (! $removing && ! $other->isEnabled())) {
                continue;
            }
            foreach ($other->get('requires', []) as $id => $constraint) {
                if (strcasecmp($id, $module->get('alias', strtolower($module->getName()))) === 0
                    || strcasecmp($id, $module->getName()) === 0) {
                    throw new RuntimeException('Bu modüle bağımlı modül var: '.$other->getName());
                }
            }
            $package = $module->getComposerAttr('name');
            if ($package && array_key_exists($package, $other->getComposerAttr('require') ?? [])) {
                throw new RuntimeException('Bu modüle Composer bağımlılığı var: '.$other->getName());
            }
        }
    }

    /** @param array<string, mixed> $composer */
    private function resolveComposer(array $composer): void
    {
        if (! InstalledVersions::isInstalled('wikimedia/composer-merge-plugin')) {
            throw new RuntimeException('Modül Composer yükleyicisi kurulu değil; composer-merge-plugin gereklidir.');
        }
        $required = array_values(array_filter(array_keys($composer['require'] ?? []),
            fn (string $package): bool => $package !== 'php' && ! str_starts_with($package, 'ext-')));
        $binary = config('module-distribution.composer_binary');
        $arguments = str_ends_with($binary, '.phar') ? [PHP_BINARY, $binary] : [$binary];
        if ($required === []) {
            $this->command([...$arguments, 'dump-autoload', '--no-scripts', '--no-interaction']);
        } else {
            $this->command([...$arguments, 'update', ...$required, '--with-dependencies', '--minimal-changes', '--no-scripts', '--no-interaction']);
        }
    }

    private function activate(ModuleInstance $module, bool $installing, ?Closure $progress = null): void
    {
        $composer = json_decode(file_get_contents($module->getPath().'/composer.json'), true, 64, JSON_THROW_ON_ERROR);
        $this->compatibility->check([
            'php' => $composer['require']['php'] ?? '*',
            'laravel' => $composer['require']['laravel/framework'] ?? '*',
            'modules' => $module->get('requires', []),
        ]);
        $module->disable();
        $progress?->__invoke(4, 'Composer bağımlılıkları ve autoload hazırlanıyor');
        $this->resolveComposer($composer);
        $progress?->__invoke(5, 'Kurulum önbelleği temizleniyor');
        $this->artisan(['optimize:clear']);
        $progress?->__invoke(6, 'Modül migration işlemleri uygulanıyor');
        $migrationPath = $module->getPath().'/database/migrations';
        if (is_dir($migrationPath)) {
            $this->artisan(['migrate', '--path='.$migrationPath, '--realpath', '--force']);
        }
        $pending = $this->workingPath().'/pending-'.$module->getName().'.json';
        $progress?->__invoke(7, 'Tanımlı kurulum verileri hazırlanıyor');
        if ($installing || is_file($pending)) {
            foreach ($module->get('panel', [])['install']['seeders'] ?? [] as $seeder) {
                if (! is_string($seeder) || ! str_starts_with($seeder, 'Modules'.chr(92).$module->getName().chr(92).'Database'.chr(92).'Seeders'.chr(92))
                    || ! preg_match('/^[A-Za-z0-9_\\\\]+$/D', $seeder)) {
                    throw new RuntimeException('Kurulum seeder sınıfı geçersiz.');
                }
                $this->artisan(['db:seed', '--class='.$seeder, '--force']);
            }
        }
        $progress?->__invoke(8, 'Modül arayüz dosyaları yayınlanıyor');
        $this->assets->publish($module);
        $progress?->__invoke(9, 'Modül etkinleştiriliyor');
        $module->enable();
        try {
            $progress?->__invoke(10, 'Modül izinleri senkronize ediliyor');
            $this->artisan(['module:permissions']);
            $progress?->__invoke(11, 'Son önbellek temizliği yapılıyor');
            $this->artisan(['optimize:clear']);
            if (is_file($pending)) {
                unlink($pending);
            }
        } catch (Throwable $exception) {
            $module->disable();
            throw $exception;
        }
    }

    /** @param array<string, mixed> $entry */
    public function install(array $entry, int $actor, ?Closure $progress = null): void
    {
        $this->run('install', $entry['id'], $actor, function () use ($entry, $progress): void {
            $progress?->__invoke(0, 'Uyumluluk ve mevcut kurulum kontrol ediliyor');
            $this->compatibility->check($entry['requires']);
            if (Module::find($entry['id'])) {
                throw new RuntimeException('Modül zaten kurulu. Sürüm yükseltme bu akışta desteklenmiyor.');
            }
            $temporary = $this->workingPath().'/staging-'.Str::uuid();
            $filesystem = new Filesystem;
            $filesystem->ensureDirectoryExists($temporary);
            try {
                $progress?->__invoke(1, 'Modül paketi indiriliyor');
                $bytes = $this->download->get($entry['download']['url'], config('module-distribution.max_download_bytes'));
                if (! hash_equals($entry['download']['sha256'], hash('sha256', $bytes))) {
                    throw new RuntimeException('Modül ZIP SHA-256 doğrulaması başarısız.');
                }
                file_put_contents($temporary.'/module.zip', $bytes);
                $progress?->__invoke(2, 'Paket doğrulanıyor ve açılıyor');
                $metadata = $this->archive->extract($temporary.'/module.zip', $temporary.'/module', $entry);
                $name = $metadata['module']['name'];
                $destination = $this->root().DIRECTORY_SEPARATOR.$name;
                if (file_exists($destination) || is_link($destination)) {
                    throw new RuntimeException('Hedef modül klasörü zaten var.');
                }
                $candidate = new ModuleInstance(app(), $name, $temporary.'/module');
                $this->assets->files($candidate);
                $progress?->__invoke(3, 'Modül dosyaları yerleştiriliyor');
                // Clear stale activation before the module becomes discoverable without its autoload.
                $candidate->disable();
                if (! rename($temporary.'/module', $destination)) {
                    throw new RuntimeException('Modül klasörü taşınamadı.');
                }
                $module = new ModuleInstance(app(), $name, $destination);
                file_put_contents($this->workingPath().'/pending-'.$name.'.json', json_encode(['version' => $entry['version']], JSON_THROW_ON_ERROR));
                try {
                    $this->activate($module, true, $progress);
                } catch (Throwable $exception) {
                    $module->disable();
                    throw new RuntimeException('Kurulum tamamlanamadı; modül devre dışı tutuldu. İçerik verileri korundu. '.$exception->getMessage(), previous: $exception);
                }
            } finally {
                $filesystem->deleteDirectory($temporary);
            }
        });
    }

    public function enable(string $name, int $actor): void
    {
        $this->run('enable', $name, $actor, function () use ($name): void {
            $module = Module::findOrFail($name);
            $this->assertLocal($module);
            $this->activate($module, false);
        });
    }

    public function disable(string $name, int $actor): void
    {
        $this->run('disable', $name, $actor, function () use ($name): void {
            $module = Module::findOrFail($name);
            $this->assertLocal($module);
            $this->assertNoDependents($module, false);
            $module->disable();
            $this->artisan(['optimize:clear']);
        });
    }

    public function remove(string $name, int $actor): void
    {
        $this->run('remove', $name, $actor, function () use ($name): void {
            $module = Module::findOrFail($name);
            $this->assertLocal($module);
            $this->assertNoDependents($module, true);
            $module->disable();
            $original = $module->getPath();
            $backup = $this->workingPath().'/removed-'.$module->getName().'-'.Str::uuid();
            if (! rename($original, $backup)) {
                throw new RuntimeException('Modül kaldırma yedeğine taşınamadı.');
            }
            try {
                $binary = config('module-distribution.composer_binary');
                $arguments = str_ends_with($binary, '.phar') ? [PHP_BINARY, $binary] : [$binary];
                $this->command([...$arguments, 'dump-autoload', '--no-scripts', '--no-interaction']);
                $this->artisan(['optimize:clear']);
            } catch (Throwable $exception) {
                rename($backup, $original);
                throw $exception;
            }
        });
    }
}
