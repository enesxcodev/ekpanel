<?php

namespace Modules\Projects\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Projects\Models\ProjectCategory;
use Modules\Projects\Models\ProjectCategoryTranslation;

/** @extends Factory<ProjectCategory> */
class ProjectCategoryFactory extends Factory
{
    protected $model = ProjectCategory::class;

    public function definition(): array
    {
        return ['icon' => null];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (ProjectCategory $category): void {
            $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
            ProjectCategoryTranslation::query()->create(['project_category_id' => $category->id, 'lang_id' => $language->id, 'name' => fake()->words(2, true), 'slug' => fake()->unique()->slug(3), 'extra_fields' => []]);
        });
    }
}
