<?php

namespace Modules\Blog\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Blog\Models\BlogCategory;
use Modules\Blog\Models\BlogCategoryTranslation;

class BlogCategoryFactory extends Factory
{
    protected $model = BlogCategory::class;

    public function configure(): static
    {
        return $this->afterMaking(function (BlogCategory $record): void {
            $fields = array_intersect_key($record->getAttributes(), array_flip(['name', 'slug', 'extra_fields']));
            foreach (array_keys($fields) as $field) {
                $record->offsetUnset($field);
            }
            $record->setRelation('factoryTranslation', new BlogCategoryTranslation($fields));
        })->afterCreating(function (BlogCategory $record): void {
            $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
            $record->translations()->create(['lang_id' => $language->id, ...$record->getRelation('factoryTranslation')->toArray()]);
            $record->unsetRelation('factoryTranslation');
            $record->load('translations.language');
        });
    }

    public function definition(): array
    {
        $name = fake()->unique()->words(2, true);

        return ['parent_id' => null, 'name' => $name, 'slug' => str($name)->slug()->toString(), 'sort_order' => 0, 'extra_fields' => []];
    }
}
