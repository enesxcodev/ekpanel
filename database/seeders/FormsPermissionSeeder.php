<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class FormsPermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['forms.menu', 'forms.view', 'forms.create', 'forms.update', 'forms.delete', 'forms.submissions.view', 'forms.submissions.update', 'forms.submissions.delete'] as $permission) {
            Permission::findOrCreate($permission, 'admin');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
