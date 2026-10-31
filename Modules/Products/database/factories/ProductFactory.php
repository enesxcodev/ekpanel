<?php

namespace Modules\Products\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Products\Models\Product;
use Modules\Products\Models\ProductTranslation;

/** @extends Factory<Product> */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function configure(): static
    {
        return $this->afterMaking(function (Product $record): void {
            $fields = array_intersect_key($record->getAttributes(), array_flip(['title', 'slug', 'short_description', 'content', 'extra_fields']));
            foreach (array_keys($fields) as $field) {
                $record->offsetUnset($field);
            }
            $record->setRelation('factoryTranslation', new ProductTranslation($fields));
        })->afterCreating(function (Product $record): void {
            $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
            $record->translations()->create(['lang_id' => $language->id, ...$record->getRelation('factoryTranslation')->toArray()]);
            $record->unsetRelation('factoryTranslation');
            $record->load('translations.language');
        });
    }

    public function definition(): array
    {
        $title = fake()->words(3, true);

        return ['title' => $title, 'product_category_id' => null, 'image' => null, 'slug' => str($title)->slug()->toString().'-'.fake()->unique()->numerify('####'), 'short_description' => fake()->sentence(), 'content' => fake()->paragraph(), 'sort_order' => 0, 'is_active' => true, 'extra_fields' => []];
    }
}
