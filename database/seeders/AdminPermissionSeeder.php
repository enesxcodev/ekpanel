<?php

namespace Database\Seeders;

use App\Enums\AdminType\AdminTypeEnum;
use App\Models\Admin\Admin;
use App\Models\Settings\SettingSection;
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
        $this->call(MediaPermissionSeeder::class);
        $this->call(FormsPermissionSeeder::class);
        foreach (['admins.menu', 'admins.view', 'admins.create', 'admins.update'] as $permission) {
            Permission::findOrCreate($permission, 'admin');
        }
        Role::findOrCreate('super-admin', 'admin');
        Role::findOrCreate('customer-admin', 'admin');
        app(ModulePanelService::class)->syncPermissions();
        if (Schema::hasTable('setting_sections')) {
            app(SettingConfigurationService::class)->syncPermissions();
        }

        $defaultAdminPermissions = [
            'admins.menu', 'admins.view', 'admins.create', 'admins.update',
            'settings.menu', 'settings.view', 'settings.update',
            'media.menu', 'media.view', 'media.create', 'media.update', 'media.delete',
            'forms.menu', 'forms.view', 'forms.create', 'forms.update',
            'forms.submissions.view', 'forms.submissions.update',
        ];

        $editableSettingsSections = SettingSection::query()
            ->whereIn('key', ['site', 'seo', 'contact', 'social'])
            ->pluck('key')
            ->flatMap(fn (string $key): array => [
                'settings.sections.'.$key.'.view',
                'settings.sections.'.$key.'.update',
            ])
            ->all();

        Role::query()
            ->where('name', 'customer-admin')
            ->where('guard_name', 'admin')
            ->firstOrFail()
            ->givePermissionTo([...$defaultAdminPermissions, ...$editableSettingsSections]);

        Admin::query()->each(function (Admin $admin): void {
            if ($admin->roles()->doesntExist()) {
                $admin->assignRole($admin->type === AdminTypeEnum::SuperAdmin ? 'super-admin' : 'customer-admin');
            }
        });
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
