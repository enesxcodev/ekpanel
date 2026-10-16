<?php

namespace Database\Seeders;

use App\Enums\AdminType\AdminTypeEnum;
use App\Models\Admin\Admin;
use App\Services\Admin\Modules\ModulePanelService;
use App\Services\Settings\SettingConfigurationService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class AdminPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach (['users.menu', 'users.view', 'users.create', 'users.update'] as $permission) {
            Permission::findOrCreate($permission, 'admin');
        }
        Role::findOrCreate('super-admin', 'admin');
        Role::findOrCreate('customer-admin', 'admin');
        app(ModulePanelService::class)->syncPermissions();
        if (Schema::hasTable('setting_sections')) {
            app(SettingConfigurationService::class)->syncPermissions();
        }
        Admin::query()->each(function (Admin $admin): void {
            if ($admin->roles()->doesntExist()) {
                $admin->assignRole($admin->type === AdminTypeEnum::SuperAdmin ? 'super-admin' : 'customer-admin');
            }
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
