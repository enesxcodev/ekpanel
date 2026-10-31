<?php

namespace Modules\Documents\Database\Factories;

use App\Models\Settings\Language;
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
        return ['icon' => null];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (DocumentCategory $category): void {
            $language = Language::query()->where('code', 'tr')->firstOrFail();
            $category->translations()->create([
                'lang_id' => $language->id,
                'name' => fake()->unique()->words(2, true),
                'extra_fields' => [],
            ]);
        });
    }
}
