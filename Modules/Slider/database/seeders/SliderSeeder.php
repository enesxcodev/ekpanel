<?php

namespace Modules\Slider\Database\Seeders;

use Illuminate\Database\Seeder;
use Nwidart\Modules\Facades\Module;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class SliderSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Module::findOrFail('Slider')->get('panel')['permissions'] as $permission) {
            Permission::findOrCreate($permission, 'admin');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
