<?php

namespace App\Services\Admin\Dashboard;

use App\Models\Admin\Admin;
use Illuminate\Support\Facades\DB;

class DashboardPreferenceService
{
    /** @return array{widget_order: list<string>, revision: int}|null */
    public function find(Admin $admin): ?array
    {
        $row = DB::table('admin_dashboard_preferences')->where('admin_id', $admin->id)->first();

        return $row ? ['widget_order' => json_decode($row->widget_order, true, flags: JSON_THROW_ON_ERROR), 'revision' => (int) $row->revision] : null;
    }

    /**
     * @param  list<string>  $selected
     * @param  list<string>  $accessible
     * @return array{widget_order: list<string>, revision: int}
     */
    public function save(Admin $admin, array $selected, int $revision, array $accessible): array
    {
        return DB::transaction(function () use ($admin, $selected, $revision, $accessible): array {
            DB::table('admin_dashboard_preferences')->insertOrIgnore([
                'admin_id' => $admin->id, 'widget_order' => '[]', 'revision' => 0,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            $existing = DB::table('admin_dashboard_preferences')->where('admin_id', $admin->id)->lockForUpdate()->first();
            abort_if((int) $existing->revision !== $revision, 409, 'Düzen başka bir sekmede değişti. Sayfayı yenileyip tekrar deneyin.');
            $oldOrder = json_decode($existing->widget_order, true, flags: JSON_THROW_ON_ERROR);
            $remaining = $selected;
            $merged = [];
            foreach ($oldOrder as $id) {
                if (! in_array($id, $accessible, true)) {
                    $merged[] = $id;
                } elseif ($remaining !== []) {
                    $merged[] = array_shift($remaining);
                }
            }
            $merged = array_merge($merged, $remaining);
            $updated = DB::table('admin_dashboard_preferences')
                ->where('admin_id', $admin->id)->where('revision', $revision)
                ->update(['widget_order' => json_encode($merged, JSON_THROW_ON_ERROR), 'revision' => $revision + 1, 'updated_at' => now()]);
            abort_unless($updated === 1, 409, 'Düzen başka bir sekmede değişti. Sayfayı yenileyip tekrar deneyin.');

            return ['widget_order' => $selected, 'revision' => $revision + 1];
        }, 3);
    }
}
