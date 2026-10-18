<?php

namespace Modules\Documents\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Documents\Models\Document;
use Modules\Documents\Models\DocumentCategory;

class DocumentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Document::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'category_id' => DocumentCategory::factory(),
            'name' => fake()->sentence(),
            'slug' => fake()->unique()->slug(),
            'content' => fake()->paragraphs(2, true),
            'file' => null,
            'status' => 'draft',
            'extra_fields' => [],
        ];
    }

    public function uncategorized(): static
    {
        return $this->state(['category_id' => null]);
    }
}
