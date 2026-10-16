<?php

namespace Modules\Sector\Database\Seeders;

use Illuminate\Database\Seeder;

class SectorDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(SectorSeeder::class);
    }
}
