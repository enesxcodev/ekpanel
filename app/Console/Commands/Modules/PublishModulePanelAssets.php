<?php

namespace App\Console\Commands\Modules;

use App\Services\Admin\Modules\ModulePanelAssets;
use Illuminate\Console\Command;
use Nwidart\Modules\Exceptions\ModuleNotFoundException;
use Nwidart\Modules\Facades\Module;
use RuntimeException;

class PublishModulePanelAssets extends Command
{
    protected $signature = 'module:panel-assets {module? : Modül adı; boşsa tüm kurulu modüller}';

    protected $description = 'Derlenmiş modül panel assetlerini sürümlü public konumuna yayınlar';

    public function handle(ModulePanelAssets $assets): int
    {
        try {
            $modules = $this->argument('module') ? [Module::findOrFail($this->argument('module'))] : Module::all();
            foreach ($modules as $module) {
                $count = $assets->publish($module);
                $this->info($module->getName().': '.$count.' dosya yayınlandı.');
            }
        } catch (RuntimeException|ModuleNotFoundException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }
}
