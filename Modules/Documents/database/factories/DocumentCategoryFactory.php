<?php

namespace Modules\Documents\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Documents\Models\DocumentCategory;

class DocumentCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = DocumentCategory::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return ['name' => fake()->unique()->words(2, true), 'icon' => null, 'extra_fields' => []];
    }
}
