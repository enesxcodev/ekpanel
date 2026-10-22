<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        foreach (['cities', 'towns', 'districts', 'neighbourhoods', 'streets'] as $entity) {
            Schema::table('konum_'.$entity, function (Blueprint $table): void {
                $table->boolean('is_consistent')->default(false);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['cities', 'towns', 'districts', 'neighbourhoods', 'streets'] as $entity) {
            Schema::table('konum_'.$entity, function (Blueprint $table): void {
                $table->dropColumn('is_consistent');
            });
        }
    }
};
