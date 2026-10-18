<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->renameTables('gallery', 'galeriler');
        $this->moveUsages('Gallery', 'Galeriler', 'gallery', 'galeriler');
        $this->copyPermissions('gallery', 'galeriler');
    }

    public function down(): void
    {
        $this->renameTables('galeriler', 'gallery');
        $this->moveUsages('Galeriler', 'Gallery', 'galeriler', 'gallery');
        $this->copyPermissions('galeriler', 'gallery');
    }

    private function renameTables(string $from, string $to): void
    {
        foreach (['categories', 'items'] as $suffix) {
            if (Schema::hasTable($from.'_'.$suffix) && Schema::hasTable($to.'_'.$suffix)) {
                throw new RuntimeException('Her iki içerik tablosu da mevcut; veri üzerine yazılmasını önlemek için taşıma durduruldu.');
            }
        }
        foreach (['categories', 'items'] as $suffix) {
            if (Schema::hasTable($from.'_'.$suffix)) {
                Schema::rename($from.'_'.$suffix, $to.'_'.$suffix);
            }
        }
        if (Schema::hasColumn($to.'_items', $from.'_category_id')) {
            Schema::table($to.'_items', function (Blueprint $table) use ($from, $to): void {
                $table->renameColumn($from.'_category_id', $to.'_category_id');
            });
        }
        $oldIndex = $from.'_items_'.$from.'_category_id_sort_order_id_index';
        if (Schema::hasIndex($to.'_items', $oldIndex)) {
            Schema::table($to.'_items', function (Blueprint $table) use ($to, $oldIndex): void {
                $table->renameIndex($oldIndex, $to.'_items_'.$to.'_category_id_sort_order_id_index');
            });
        }
    }

    private function moveUsages(string $fromModule, string $toModule, string $fromRoute, string $toRoute): void
    {
        foreach (['Category', 'Item'] as $model) {
            $oldOwner = 'Modules\\'.$fromModule.'\\Models\\'.$fromModule.$model;
            $newOwner = 'Modules\\'.$toModule.'\\Models\\'.$toModule.$model;
            $route = $model === 'Category' ? '.categories.edit' : '.edit';
            DB::table('media_usages')->where('owner_type', $oldOwner)->update([
                'owner_type' => $newOwner, 'route_name' => 'admin.'.$toRoute.$route,
            ]);
        }
    }

    private function copyPermissions(string $from, string $to): void
    {
        $tables = config('permission.table_names');
        $permissionKey = config('permission.column_names.permission_pivot_key') ?: 'permission_id';
        $suffixes = ['menu', 'view', 'create', 'update', 'delete', 'categories.menu', 'categories.view', 'categories.create', 'categories.update', 'categories.delete'];
        foreach ($suffixes as $suffix) {
            $old = DB::table($tables['permissions'])->where('guard_name', 'admin')->where('name', $from.'.'.$suffix)->first();
            if ($old === null) {
                continue;
            }
            $target = DB::table($tables['permissions'])->where('guard_name', 'admin')->where('name', $to.'.'.$suffix)->first();
            $targetId = $target?->id ?? DB::table($tables['permissions'])->insertGetId([
                'name' => $to.'.'.$suffix, 'guard_name' => 'admin', 'created_at' => now(), 'updated_at' => now(),
            ]);
            foreach (['model_has_permissions', 'role_has_permissions'] as $pivot) {
                foreach (DB::table($tables[$pivot])->where($permissionKey, $old->id)->get() as $assignment) {
                    $row = (array) $assignment;
                    $row[$permissionKey] = $targetId;
                    DB::table($tables[$pivot])->insertOrIgnore($row);
                }
            }
        }
        app('cache')->store(config('permission.cache.store') !== 'default' ? config('permission.cache.store') : null)->forget(config('permission.cache.key'));
    }
};
