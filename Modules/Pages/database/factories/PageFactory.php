<?php

namespace Modules\Pages\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Pages\Models\Page;
use Modules\Pages\Models\PageCategory;

class PageFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Page::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'page_category_id' => PageCategory::factory(),
            'title' => fake()->sentence(3),
            'short_description' => fake()->sentence(),
            'content' => fake()->paragraph(),
            'image' => null,
            'slug' => fake()->unique()->slug(),
            'status' => 'published',
            'sort_order' => 0,
            'extra_fields' => [],
        ];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Page $page): void {
            $language = Language::query()->where('code', 'tr')->where('is_active', true)->first();
            if ($language === null) {
                return;
            }

            $page->translations()->create([
                'lang_id' => $language->id,
                'scope_id' => $page->page_category_id ?? 0,
                'title' => $page->title,
                'slug' => $page->slug,
                'short_description' => $page->short_description,
                'content' => $page->content,
                'meta_title' => $page->meta_title,
                'meta_keywords' => $page->meta_keywords,
                'meta_description' => $page->meta_description,
                'extra_fields' => $page->extra_fields,
            ]);
        });
    }
}
