<?php

namespace Modules\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Products\Models\ProductCategory;

/** @extends Factory<ProductCategory> */
class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    /** @return array{name: string} */
    public function definition(): array
    {
        return ['name' => fake()->words(2, true), 'extra_fields' => null];
    }
}
