<?php

namespace Modules\Products\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Products\Models\Product;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        return ['title' => fake()->words(3, true), 'product_category_id' => null, 'image' => null, 'slug' => fake()->unique()->slug(), 'sort_order' => 0, 'is_active' => true, 'extra_fields' => []];
    }
}
