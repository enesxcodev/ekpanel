<?php

namespace Modules\Services\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Services\Models\Service;

/** @extends Factory<Service> */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function definition(): array
    {
        return ['title' => fake()->words(3, true), 'service_category_id' => null, 'image' => null, 'icon' => null, 'is_active' => true, 'extra_fields' => []];
    }
}
