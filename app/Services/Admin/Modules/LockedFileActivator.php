<?php

namespace App\Services\Admin\Modules;

use Closure;
use Illuminate\Container\Container;
use Nwidart\Modules\Contracts\ActivatorInterface;
use Nwidart\Modules\Module;
use RuntimeException;

class LockedFileActivator implements ActivatorInterface
{
    private string $statusesFile;

    public function __construct(Container $app)
    {
        $this->statusesFile = $app['config']->get('modules.activators.file.statuses-file');
    }

    public function getStatusesFilePath(): string
    {
        return $this->statusesFile;
    }

    public function enable(Module $module): void
    {
        $this->setActive($module, true);
    }

    public function disable(Module $module): void
    {
        $this->setActive($module, false);
    }

    public function hasStatus(Module|string $module, bool $status): bool
    {
        $name = $module instanceof Module ? $module->getName() : $module;

        return $this->locked(LOCK_SH, fn (): bool => ($this->read()[$name] ?? false) === $status);
    }

    public function setActive(Module $module, bool $active): void
    {
        $this->setActiveByName($module->getName(), $active);
    }

    public function setActiveByName(string $name, bool $active): void
    {
        $this->locked(LOCK_EX, function () use ($name, $active): void {
            $statuses = $this->read();
            $statuses[$name] = $active;
            $this->write($statuses);
        });
    }

    public function delete(Module $module): void
    {
        $this->locked(LOCK_EX, function () use ($module): void {
            $statuses = $this->read();
            unset($statuses[$module->getName()]);
            $this->write($statuses);
        });
    }

    public function reset(): void
    {
        $this->locked(LOCK_EX, fn () => $this->write([]));
    }

    private function locked(int $mode, Closure $operation): mixed
    {
        // The lock must survive replacement of the JSON file and cover readers too.
        $handle = $this->attempt(fn () => fopen($this->statusesFile.'.lock', 'c+b'), 'Durum kilidi açılamadı');
        try {
            $this->attempt(fn (): bool => flock($handle, $mode | LOCK_NB), 'Durum kilidi alınamadı');

            return $operation();
        } finally {
            flock($handle, LOCK_UN);
            fclose($handle);
        }
    }

    /** @return array<string, bool> */
    private function read(): array
    {
        clearstatcache(true, $this->statusesFile);
        if (! file_exists($this->statusesFile)) {
            return [];
        }
        if (! is_file($this->statusesFile)) {
            throw new RuntimeException('Modül durum yolu normal bir dosya olmalıdır: '.$this->statusesFile);
        }
        $handle = $this->attempt(fn () => fopen($this->statusesFile, 'rb'), 'Modül durumları okunamadı');
        try {
            $content = stream_get_contents($handle);
        } finally {
            fclose($handle);
        }
        $statuses = json_decode($content, true, 512, JSON_THROW_ON_ERROR);
        if (! is_array($statuses) || array_filter($statuses, fn (mixed $value): bool => ! is_bool($value)) !== []) {
            throw new RuntimeException('Modül durum dosyası geçersiz; mevcut içerik korunuyor.');
        }

        return $statuses;
    }

    /** @param array<string, bool> $statuses */
    private function write(array $statuses): void
    {
        $content = json_encode((object) $statuses, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR);
        $temporary = dirname($this->statusesFile).'/.module-status-'.bin2hex(random_bytes(12)).'.tmp';
        $handle = $this->attempt(fn () => fopen($temporary, 'x+b'), 'Geçici durum dosyası oluşturulamadı');
        try {
            try {
                $offset = 0;
                while ($offset < strlen($content)) {
                    $written = fwrite($handle, substr($content, $offset));
                    if ($written === false || $written === 0) {
                        throw new RuntimeException('Modül durumları tam yazılamadı; önceki dosya korunuyor.');
                    }
                    $offset += $written;
                }
                if (! fflush($handle)) {
                    throw new RuntimeException('Modül durumları diske aktarılamadı; önceki dosya korunuyor.');
                }
            } finally {
                fclose($handle);
            }
            if (PHP_OS_FAMILY !== 'Windows' && is_file($this->statusesFile)) {
                chmod($temporary, fileperms($this->statusesFile) & 0777);
            }
            // Never truncate or unlink a file another Windows process may have mapped.
            $this->attempt(fn (): bool => rename($temporary, $this->statusesFile), 'Modül durum dosyası değiştirilemedi; önceki dosya korunuyor');
        } finally {
            if (is_file($temporary)) {
                unlink($temporary);
            }
        }
    }

    private function attempt(Closure $operation, string $message): mixed
    {
        $detail = 'Dosya başka bir süreç tarafından kullanılıyor.';
        for ($attempt = 0; $attempt < 100; $attempt++) {
            set_error_handler(function (int $severity, string $warning) use (&$detail): bool {
                $detail = $warning;

                return true;
            });
            try {
                $result = $operation();
            } finally {
                restore_error_handler();
            }
            if ($result !== false) {
                return $result;
            }
            usleep(20000);
        }
        throw new RuntimeException($message.': '.$this->statusesFile.'. '.$detail);
    }
}
