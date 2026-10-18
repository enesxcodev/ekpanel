<?php

namespace Modules\Galeriler\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Galeriler\Models\GalerilerCategory;

/** @extends Factory<GalerilerCategory> */
class GalerilerCategoryFactory extends Factory
{
    protected $model = GalerilerCategory::class;

    public function definition(): array
    {
        return ['name' => fake()->words(2, true), 'parent_id' => null, 'icon' => null, 'short_description' => null, 'extra_fields' => []];
    }
}
