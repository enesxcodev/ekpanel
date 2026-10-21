<?php

namespace Modules\Store\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Store\Models\StoreCategory;

class StoreCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = StoreCategory::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return ['name' => $name, 'slug' => str($name)->slug()->toString(), 'sort_order' => 0, 'is_active' => true, 'extra_fields' => []];
    }
}
