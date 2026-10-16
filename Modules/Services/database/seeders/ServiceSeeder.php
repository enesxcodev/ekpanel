<?php

namespace Modules\Services\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Services\Models\Service;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        Service::factory()->count(5)->create();
    }
}
