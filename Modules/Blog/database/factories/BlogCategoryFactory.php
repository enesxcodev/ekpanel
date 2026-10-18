<?php

namespace Modules\Blog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Blog\Models\BlogCategory;

class BlogCategoryFactory extends Factory
{
    protected $model = BlogCategory::class;

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return ['parent_id' => null, 'name' => $name, 'slug' => str($name)->slug()->toString(), 'sort_order' => 0, 'extra_fields' => []];
    }
}
