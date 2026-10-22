<?php

namespace Modules\Branches\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Branches\Models\BranchCategory;

class BranchCategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['slug' => 'branch', 'name' => 'Şubeler'], ['slug' => 'dealer', 'name' => 'Bayiler']] as $index => $category) {
            BranchCategory::query()->firstOrCreate(
                ['slug' => $category['slug']],
                ['name' => $category['name'], 'sort_order' => $index + 1, 'is_active' => true],
            );
        }
    }
}
