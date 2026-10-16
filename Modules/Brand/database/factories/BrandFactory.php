<?php

namespace Modules\Brand\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Brand\Models\Brand;

/** @extends Factory<Brand> */
class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        return ['name' => fake()->company(), 'image' => 'brand/images/example.png', 'short_description' => null, 'link' => null, 'extra_fields' => []];
    }
}
