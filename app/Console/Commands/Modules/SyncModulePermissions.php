<?php

namespace App\Console\Commands\Modules;

use App\Services\Admin\Modules\ModulePanelService;
use Illuminate\Console\Command;

class SyncModulePermissions extends Command
{
    protected $signature = 'module:permissions';

    protected $description = 'Etkin modüllerin panel izinlerini admin guard için kaydeder';

    public function handle(ModulePanelService $panel): int
    {
        $count = $panel->syncPermissions();
        $this->info($count.' modül izni kaydedildi.');

        return self::SUCCESS;
    }
}
