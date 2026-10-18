<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class MediaPermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['menu', 'view', 'create', 'update', 'delete'] as $action) {
            Permission::findOrCreate('media.'.$action, 'admin');
        }
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
