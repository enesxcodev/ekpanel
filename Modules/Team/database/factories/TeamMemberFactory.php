<?php

namespace Modules\Team\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Team\Models\TeamMember;

/** @extends Factory<TeamMember> */
class TeamMemberFactory extends Factory
{
    protected $model = TeamMember::class;

    public function definition(): array
    {
        return ['name' => fake()->name(), 'title' => fake()->jobTitle(), 'image' => null, 'is_active' => true, 'extra_fields' => []];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
