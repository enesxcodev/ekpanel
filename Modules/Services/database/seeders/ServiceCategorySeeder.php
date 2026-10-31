<?php

namespace Modules\Services\Database\Seeders;

use App\Models\Settings\Language;
use Illuminate\Database\Seeder;
use Modules\Services\Models\ServiceCategory;

class ServiceCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
        if (! ServiceCategory::query()->whereHas('translations', fn ($query) => $query->where('lang_id', $language->id)->where('name', 'Genel'))->exists()) {
            ServiceCategory::query()->create([])->translations()->create(['lang_id' => $language->id, 'name' => 'Genel']);
        }
    }
}
