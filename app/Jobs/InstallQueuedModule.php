<?php

namespace App\Jobs;

use App\Services\Admin\Modules\ModuleInstallations;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Process;
use Throwable;

class InstallQueuedModule implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public int $timeout = 3500;

    public bool $failOnTimeout = true;

    public function __construct(public string $installationId, public int $index)
    {
        $this->onConnection('module-installations')->onQueue('module-installations');
    }

    /**
     * Execute the job.
     */
    public function handle(ModuleInstallations $installations): void
    {
        if (! $installations->claim($this->installationId, $this->index)) {
            return;
        }
        try {
            $result = Process::path(base_path())->timeout(3300)->run([
                PHP_BINARY, base_path('artisan'), 'module:install-queued', $this->installationId, (string) $this->index, '--no-interaction',
            ]);
            $error = $result->successful() ? null : 'Kurulum süreci başarısız: '.mb_substr(trim($result->errorOutput() ?: $result->output()), 0, 2000);
            $installations->finish($this->installationId, $this->index, $error);
        } catch (Throwable $exception) {
            $installations->finish($this->installationId, $this->index, 'Kurulum süreci kesildi: '.$exception->getMessage());
            throw $exception;
        }
    }

    public function failed(?Throwable $exception): void
    {
        app(ModuleInstallations::class)->interrupt($this->installationId, $this->index, 'Kuyruk işi kesildi: '.($exception?->getMessage() ?? 'Bilinmeyen hata'));
    }
}
