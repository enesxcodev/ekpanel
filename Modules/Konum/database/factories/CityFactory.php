<?php

namespace Modules\Konum\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Konum\Models\City;

/** @extends Factory<City> */
class CityFactory extends Factory
{
    protected $model = City::class;

    public function definition(): array
    {
        return [
            'id' => fake()->unique()->numberBetween(1, 100000000),
            'name' => fake()->city(),
            'is_active' => true,
            'source_row_hash' => str_repeat('0', 64),
            'plate_code' => '01',
            'towns' => '[]',
            'lat' => '37.0',
            'lon' => '35.0',
            'boundingbox' => '[]',
        ];
    }
}
