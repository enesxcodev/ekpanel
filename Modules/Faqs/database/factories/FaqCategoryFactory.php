<?php

namespace Modules\Faqs\Database\Factories;

use App\Models\Settings\Language;
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
        return [];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (FaqCategory $category): void {
            $category->translations()->create([
                'lang_id' => Language::query()->where('code', 'tr')->firstOrFail()->id,
                'name' => fake()->unique()->words(2, true),
                'extra_fields' => [],
            ]);
        });
    }
}
