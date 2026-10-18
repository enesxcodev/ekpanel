<?php

namespace Modules\Faqs\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Faqs\Models\FaqCategory;

class FaqCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = FaqCategory::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return ['name' => fake()->unique()->words(2, true), 'extra_fields' => []];
    }
}
