<?php

namespace Modules\Branches\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Modules\Branches\Models\BranchCategory;

/** @extends Factory<BranchCategory> */
class BranchCategoryFactory extends Factory
{
    protected $model = BranchCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return ['slug' => Str::slug($name), 'name' => $name, 'is_active' => true, 'sort_order' => 0];
    }
}
