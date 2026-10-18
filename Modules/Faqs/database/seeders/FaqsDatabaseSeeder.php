<?php

namespace Modules\Faqs\Database\Seeders;

use Illuminate\Database\Seeder;

class FaqsDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            FaqCategorySeeder::class,
            FaqSeeder::class,
        ]);
    }
}
