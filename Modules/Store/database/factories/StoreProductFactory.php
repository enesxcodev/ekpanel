<?php

namespace Modules\Store\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Store\Models\StoreProduct;

class StoreProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = StoreProduct::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(3, true);

        return ['store_category_id' => null, 'title' => $name, 'slug' => str($name)->slug()->toString(), 'sku' => fake()->unique()->bothify('SKU-#####'), 'price_minor' => 10000, 'currency' => 'TRY', 'tracks_stock' => true, 'stock_quantity' => 10, 'low_stock_threshold' => 0, 'allow_backorders' => false, 'is_active' => true, 'sort_order' => 0, 'extra_fields' => []];
    }
}
