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
        foreach ($this->indexes() as $entity => $ancestors) {
            Schema::table('konum_'.$entity, function (Blueprint $table) use ($ancestors): void {
                foreach ($ancestors as $ancestor) {
                    $table->index([$ancestor, 'is_active', 'name', 'id']);
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach ($this->indexes() as $entity => $ancestors) {
            Schema::table('konum_'.$entity, function (Blueprint $table) use ($ancestors): void {
                foreach ($ancestors as $ancestor) {
                    $table->dropIndex([$ancestor, 'is_active', 'name', 'id']);
                }
            });
        }
    }

    /** @return array<string, list<string>> */
    private function indexes(): array
    {
        return ['districts' => ['city_id'], 'neighbourhoods' => ['city_id', 'town_id'], 'streets' => ['city_id', 'town_id', 'district_id']];
    }
};
