<?php

namespace Modules\Konum\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Konum\Models\Neighbourhood;
use Modules\Konum\Models\Street;

/** @extends Factory<Street> */
class StreetFactory extends Factory
{
    protected $model = Street::class;

    public function definition(): array
    {
        return [
            'id' => fake()->unique()->numberBetween(1, 100000000),
            'name' => fake()->city(),
            'is_active' => true,
            'source_row_hash' => str_repeat('0', 64),
            'neighbourhood_id' => Neighbourhood::factory(),
            'city_id' => fn (array $attributes): int => (int) Neighbourhood::query()->findOrFail($attributes['neighbourhood_id'])->city_id,
            'town_id' => fn (array $attributes): int => (int) Neighbourhood::query()->findOrFail($attributes['neighbourhood_id'])->town_id,
            'district_id' => fn (array $attributes): int => (int) Neighbourhood::query()->findOrFail($attributes['neighbourhood_id'])->district_id,
        ];
    }
}
