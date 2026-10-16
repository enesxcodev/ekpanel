<?php

namespace Modules\Team\Database\Seeders;

use Illuminate\Database\Seeder;

class TeamDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(TeamMemberSeeder::class);
    }
}
