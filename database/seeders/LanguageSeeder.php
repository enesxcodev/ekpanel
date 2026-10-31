<?php

namespace Database\Seeders;

use App\Models\Settings\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $code = config('app.locale');
        if (array_key_exists($code, config('languages.catalog'))) {
            Language::query()->firstOrCreate(['code' => $code], ['is_active' => true]);
        }
    }
}
