<?php

namespace Modules\Pages\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Pages\Models\Page;
use Modules\Pages\Models\PageCategory;

class PageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Page::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'page_category_id' => PageCategory::factory(),
            'title' => fake()->sentence(3),
            'short_description' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'image' => null,
            'slug' => fake()->unique()->slug(),
            'status' => 'published',
            'sort_order' => 0,
            'extra_fields' => [],
        ];
    }
}
