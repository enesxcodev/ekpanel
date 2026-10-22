<?php

namespace Modules\Konum\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Konum\Models\District;
use Modules\Konum\Models\Neighbourhood;

/** @extends Factory<Neighbourhood> */
class NeighbourhoodFactory extends Factory
{
    protected $model = Neighbourhood::class;

    public function definition(): array
    {
        return [
            'id' => fake()->unique()->numberBetween(1, 100000000),
            'name' => fake()->city(),
            'is_active' => true,
            'source_row_hash' => str_repeat('0', 64),
            'district_id' => District::factory(),
            'city_id' => fn (array $attributes): int => (int) District::query()->findOrFail($attributes['district_id'])->city_id,
            'town_id' => fn (array $attributes): int => (int) District::query()->findOrFail($attributes['district_id'])->town_id,
            'zip_code' => '01000',
            'streets' => '[]',
        ];
    }
}
