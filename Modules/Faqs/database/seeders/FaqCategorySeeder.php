<?php

namespace Modules\Faqs\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Faqs\Models\FaqCategory;

class FaqCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        FaqCategory::factory()->count(4)->create();
    }
}
