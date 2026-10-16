<?php

namespace Modules\Sector\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Sector\Models\Sector;

/** @extends Factory<Sector> */
class SectorFactory extends Factory
{
    protected $model = Sector::class;

    public function definition(): array
    {
        return ['title' => fake()->words(3, true), 'short_description' => null, 'content' => null, 'meta_title' => null, 'meta_keywords' => null, 'meta_description' => null, 'image' => null, 'is_active' => true, 'extra_fields' => []];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
