<?php

namespace Modules\Branches\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Branches\Models\Branch;
use Modules\Branches\Models\BranchCategory;

/** @extends Factory<Branch> */
class BranchFactory extends Factory
{
    protected $model = Branch::class;

    public function definition(): array
    {
        return [
            'branch_category_id' => BranchCategory::factory(),
            'name' => fake()->company(),
            'is_active' => true,
            'sort_order' => 0,
        ];
    }
}
