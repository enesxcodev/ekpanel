<?php

namespace Modules\Products\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Products\Models\ProductCategory;
use Modules\Products\Models\ProductCategoryTranslation;

/** @extends Factory<ProductCategory> */
class ProductCategoryFactory extends Factory
{
    protected $model = ProductCategory::class;

    public function configure(): static
    {
        return $this->afterMaking(function (ProductCategory $record): void {
            $fields = array_intersect_key($record->getAttributes(), array_flip(['name', 'extra_fields']));
            foreach (array_keys($fields) as $field) {
                $record->offsetUnset($field);
            }
            $record->setRelation('factoryTranslation', new ProductCategoryTranslation($fields));
        })->afterCreating(function (ProductCategory $record): void {
            $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
            $record->translations()->create(['lang_id' => $language->id, ...$record->getRelation('factoryTranslation')->toArray()]);
            $record->unsetRelation('factoryTranslation');
            $record->load('translations.language');
        });
    }

    /** @return array{name: string} */
    public function definition(): array
    {
        return ['name' => fake()->words(2, true), 'extra_fields' => null];
    }
}
