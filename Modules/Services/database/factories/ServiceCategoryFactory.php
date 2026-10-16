<?php

namespace Modules\Services\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Services\Models\ServiceCategory;

/** @extends Factory<ServiceCategory> */
class ServiceCategoryFactory extends Factory
{
    protected $model = ServiceCategory::class;

    /** @return array{name: string} */
    public function definition(): array
    {
        return ['name' => fake()->words(2, true), 'extra_fields' => null];
    }
}
