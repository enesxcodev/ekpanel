<?php

namespace Modules\Blog\Database\Seeders;

use App\Models\Settings\Language;
use App\Services\Admin\Modules\ModulePanelService;
use Illuminate\Database\Seeder;
use Modules\Blog\Models\BlogCategory;

class BlogDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(ModulePanelService::class)->syncPermissions();

        $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
        foreach ([['Blog', 'blog'], ['Haberler', 'haberler'], ['Duyurular', 'duyurular']] as [$name, $slug]) {
            $category = BlogCategory::query()->whereNull('parent_id')->whereHas('translations', fn ($query) => $query->where('lang_id', $language->id)->where('slug', $slug))->first();
            if ($category === null) {
                $category = BlogCategory::query()->create(['parent_id' => null, 'sort_order' => 0]);
                $category->translations()->create(['lang_id' => $language->id, 'name' => $name, 'slug' => $slug]);
            }
        }
    }
}
