<?php

namespace Modules\Careers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Careers\Models\JobPosting;

class JobPostingFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = JobPosting::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return ['title' => fake()->jobTitle(), 'location' => fake()->city(), 'employment_type' => 'full_time', 'is_active' => true];
    }
}
