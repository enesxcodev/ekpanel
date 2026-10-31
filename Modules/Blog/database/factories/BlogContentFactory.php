<?php

namespace Modules\Blog\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogContent;
use Modules\Blog\Models\BlogContentTranslation;

class BlogContentFactory extends Factory
{
    protected $model = BlogContent::class;

    public function configure(): static
    {
        return $this->afterMaking(function (BlogContent $record): void {
            $fields = array_intersect_key($record->getAttributes(), array_flip(['title', 'slug', 'short_description', 'content', 'meta_title', 'meta_description', 'extra_fields']));
            foreach (array_keys($fields) as $field) {
                $record->offsetUnset($field);
            }
            $record->setRelation('factoryTranslation', new BlogContentTranslation($fields));
        })->afterCreating(function (BlogContent $record): void {
            $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
            $record->translations()->create(['lang_id' => $language->id, ...$record->getRelation('factoryTranslation')->toArray()]);
            $record->unsetRelation('factoryTranslation');
            $record->load('translations.language');
        });
    }

    public function definition(): array
    {
        $title = fake()->sentence(4);

        return ['blog_category_id' => BlogCategory::factory(), 'title' => $title, 'slug' => str($title)->slug()->toString().'-'.fake()->unique()->numerify('####'), 'short_description' => fake()->sentence(), 'content' => fake()->paragraph(), 'status' => 'draft', 'extra_fields' => []];
    }
}
