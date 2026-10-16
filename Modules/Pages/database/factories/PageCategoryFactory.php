<?php

namespace Modules\Pages\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Pages\Models\PageCategory;

class PageCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = PageCategory::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'parent_id' => null,
            'name' => fake()->words(2, true),
            'short_description' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'sort_order' => 0,
            'extra_fields' => [],
        ];
    }
}
