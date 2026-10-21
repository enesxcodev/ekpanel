<?php

namespace App\Console\Commands\Modules;

use App\Services\Admin\Modules\ModuleInstallations;
use App\Services\Admin\Modules\ModuleManager;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Throwable;

#[Signature('module:install-queued {installation} {index}')]
#[Description('Kuyruktaki tek modülü mevcut kurucu ile yeni uygulama sürecinde kurar')]
class RunQueuedModuleInstall extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(ModuleInstallations $installations, ModuleManager $manager): int
    {
        $id = (string) $this->argument('installation');
        $index = (int) $this->argument('index');
        try {
            $item = $installations->entry($id, $index);
            $progress = fn (int $completed, string $stage) => $installations->progress($id, $index, $completed, $stage);
            if (($item['entry']['action'] ?? 'install') === 'enable') {
                $manager->enable($item['entry']['name'], (int) $item['actor'], $progress);
            } else {
                $manager->install($item['entry'], (int) $item['actor'], $progress);
            }

            return self::SUCCESS;
        } catch (Throwable $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
    }
}
