<?php

namespace Modules\Store\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Store\Models\StoreProduct;
use Modules\Store\Models\StoreVariant;

class StoreVariantFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = StoreVariant::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return ['store_product_id' => StoreProduct::factory(), 'title' => fake()->colorName(), 'sku' => fake()->unique()->bothify('VAR-#####'), 'option_values' => [], 'price_minor' => null, 'stock_quantity' => 10, 'is_active' => true, 'sort_order' => 0];
    }
}
