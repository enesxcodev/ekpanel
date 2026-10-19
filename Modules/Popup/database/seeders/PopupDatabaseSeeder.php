<?php

namespace Modules\Popup\Database\Seeders;

use Illuminate\Database\Seeder;

class PopupDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(PopupSeeder::class);
    }
}
