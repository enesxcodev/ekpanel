<?php

namespace Modules\Services\Database\Seeders;

use App\Services\Admin\Modules\ModulePanelService;
use Illuminate\Database\Seeder;

class ServicesDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        app(ModulePanelService::class)->syncPermissions();
    }
}
