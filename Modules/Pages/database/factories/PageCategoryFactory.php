<?php

namespace Modules\Pages\Database\Factories;

use App\Models\Settings\Language;
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

    public function configure(): static
    {
        return $this->afterCreating(function (PageCategory $category): void {
            $language = Language::query()->where('code', 'tr')->where('is_active', true)->first();
            if ($language === null) {
                return;
            }

            $category->translations()->create([
                'lang_id' => $language->id,
                'scope_id' => $category->parent_id ?? 0,
                'name' => $category->name,
                'slug' => $category->slug,
                'short_description' => $category->short_description,
                'extra_fields' => $category->extra_fields,
            ]);
        });
    }
}
