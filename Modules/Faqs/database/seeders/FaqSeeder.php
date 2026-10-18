<?php

namespace Modules\Faqs\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Faqs\Models\Faq;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Faq::factory()->count(8)->create();
    }
}
