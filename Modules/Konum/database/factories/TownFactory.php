<?php

namespace Modules\Konum\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Konum\Models\City;
use Modules\Konum\Models\Town;

/** @extends Factory<Town> */
class TownFactory extends Factory
{
    protected $model = Town::class;

    public function definition(): array
    {
        return [
            'id' => fake()->unique()->numberBetween(1, 100000000),
            'name' => fake()->city(),
            'is_active' => true,
            'source_row_hash' => str_repeat('0', 64),
            'city_id' => City::factory(),
            'districts' => '[]',
        ];
    }
}
