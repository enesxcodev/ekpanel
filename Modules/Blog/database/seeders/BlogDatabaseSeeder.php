<?php

namespace Modules\Blog\Database\Seeders;

use App\Services\Admin\Modules\ModulePanelService;
use Illuminate\Database\Seeder;
use Modules\Blog\Models\BlogCategory;

class BlogDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(ModulePanelService::class)->syncPermissions();

        foreach ([['Blog', 'blog'], ['Haberler', 'haberler'], ['Duyurular', 'duyurular']] as [$name, $slug]) {
            BlogCategory::query()->firstOrCreate(['parent_id' => null, 'slug' => $slug], ['name' => $name, 'sort_order' => 0]);
        }
    }
}
