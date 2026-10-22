<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('branch_categories')->where('slug', 'branch')->where('name', 'Şube')->update(['name' => 'Şubeler']);
        DB::table('branch_categories')->where('slug', 'dealer')->where('name', 'Bayi')->update(['name' => 'Bayiler']);
    }

    public function down(): void
    {
        DB::table('branch_categories')->where('slug', 'branch')->where('name', 'Şubeler')->update(['name' => 'Şube']);
        DB::table('branch_categories')->where('slug', 'dealer')->where('name', 'Bayiler')->update(['name' => 'Bayi']);
    }
};
