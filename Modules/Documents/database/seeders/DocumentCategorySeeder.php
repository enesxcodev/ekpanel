<?php

namespace Modules\Documents\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Documents\Models\DocumentCategory;

class DocumentCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (['İndirmeler', 'Belgeler', 'Sertifikalar', 'Ürün Kataloğu'] as $name) {
            DocumentCategory::query()->firstOrCreate(['name' => $name]);
        }
    }
}
