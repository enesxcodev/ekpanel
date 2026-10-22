<?php

namespace Modules\Konum\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Konum\Models\District;
use Modules\Konum\Models\Town;

/** @extends Factory<District> */
class DistrictFactory extends Factory
{
    protected $model = District::class;

    public function definition(): array
    {
        return [
            'id' => fake()->unique()->numberBetween(1, 100000000),
            'name' => fake()->city(),
            'is_active' => true,
            'source_row_hash' => str_repeat('0', 64),
            'town_id' => Town::factory(),
            'city_id' => fn (array $attributes): int => (int) Town::query()->findOrFail($attributes['town_id'])->city_id,
            'zip_code' => '01000',
            'neighbourhoods' => '[]',
        ];
    }
}
