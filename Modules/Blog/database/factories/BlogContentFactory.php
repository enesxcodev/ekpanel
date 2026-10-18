<?php

namespace Modules\Blog\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogContent;

class BlogContentFactory extends Factory
{
    protected $model = BlogContent::class;

    public function definition(): array
    {
        $title = fake()->sentence(4);

        return ['blog_category_id' => BlogCategory::factory(), 'title' => $title, 'slug' => str($title)->slug()->toString().'-'.fake()->unique()->numerify('####'), 'short_description' => fake()->sentence(), 'content' => fake()->paragraph(), 'status' => 'draft', 'extra_fields' => []];
    }
}
