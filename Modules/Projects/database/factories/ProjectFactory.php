<?php

namespace Modules\Projects\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectCategory;
use Modules\Projects\Models\ProjectTranslation;

/** @extends Factory<Project> */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return ['project_category_id' => ProjectCategory::factory(), 'project_date' => '2026-10-18', 'is_active' => false, 'sort_order' => 0];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Project $project): void {
            $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
            ProjectTranslation::query()->create(['project_id' => $project->id, 'lang_id' => $language->id, 'title' => fake()->sentence(3), 'slug' => fake()->unique()->slug(3), 'content' => null, 'extra_fields' => []]);
        });
    }
}
