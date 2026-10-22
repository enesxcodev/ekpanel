<?php

namespace Modules\Careers\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Careers\Models\JobApplication;
use Modules\Careers\Models\JobPosting;

class JobApplicationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = JobApplication::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'job_posting_id' => JobPosting::factory(),
            'candidate_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'cover_letter' => fake()->paragraph(),
            'status' => 'new',
        ];
    }
}
