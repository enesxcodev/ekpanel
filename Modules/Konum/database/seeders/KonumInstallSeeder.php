<?php

namespace Modules\Konum\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class KonumInstallSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('konum_imports')->insertOrIgnore([
            'source_commit' => config('konum.source.commit'),
            'status' => 'pending',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
