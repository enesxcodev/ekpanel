<?php

namespace Modules\Branches\Database\Seeders;

use Illuminate\Database\Seeder;

class BranchesDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(BranchCategorySeeder::class);
    }
}
