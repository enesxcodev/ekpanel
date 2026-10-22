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
        Schema::create('konum_imports', function (Blueprint $table): void {
            $table->string('source_commit', 40)->primary();
            $table->string('status', 20)->default('pending');
            $table->json('report')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();
        });
        $parents = [];
        foreach (['cities', 'towns', 'districts', 'neighbourhoods', 'streets'] as $entity) {
            Schema::create('konum_'.$entity, function (Blueprint $table) use ($entity, $parents): void {
                $table->unsignedBigInteger('id')->primary();
                foreach ($parents as $column => $parent) {
                    $table->unsignedBigInteger($column);
                    $table->foreign($column)->references('id')->on('konum_'.$parent)->restrictOnDelete();
                }
                $table->string('name')->nullable();
                if ($entity === 'cities') {
                    $table->string('plate_code', 25)->nullable();
                }
                if (in_array($entity, ['districts', 'neighbourhoods'], true)) {
                    $table->string('zip_code', 5);
                }
                $child = ['cities' => 'towns', 'towns' => 'districts', 'districts' => 'neighbourhoods', 'neighbourhoods' => 'streets'][$entity] ?? null;
                if ($child !== null) {
                    $table->json($child)->nullable();
                }
                if (in_array($entity, ['cities', 'towns'], true)) {
                    $table->string('lat')->nullable();
                    $table->string('lon')->nullable();
                    $table->longText('polygons')->nullable();
                    $table->json('boundingbox')->nullable();
                }
                $table->boolean('is_active')->default(true);
                $table->timestamps();
                $table->softDeletes();
                $table->string('source_row_hash', 64);
                $parentColumn = array_key_last($parents);
                $table->index($parentColumn === null ? ['is_active', 'name', 'id'] : [$parentColumn, 'is_active', 'name', 'id']);
            });
            $parents[['cities' => 'city_id', 'towns' => 'town_id', 'districts' => 'district_id', 'neighbourhoods' => 'neighbourhood_id', 'streets' => 'street_id'][$entity]] = $entity;
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        foreach (['streets', 'neighbourhoods', 'districts', 'towns', 'cities'] as $entity) {
            Schema::dropIfExists('konum_'.$entity);
        }
        Schema::dropIfExists('konum_imports');
    }
};
