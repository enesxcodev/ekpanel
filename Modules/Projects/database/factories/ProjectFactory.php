<?php

namespace Modules\Projects\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Projects\Models\Project;
use Modules\Projects\Models\ProjectCategory;

/** @extends Factory<Project> */
class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        return ['project_category_id' => ProjectCategory::factory(), 'title' => fake()->sentence(3), 'slug' => fake()->unique()->slug(3), 'project_date' => '2026-10-18', 'content' => null, 'is_active' => false, 'sort_order' => 0, 'extra_fields' => []];
    }
}
