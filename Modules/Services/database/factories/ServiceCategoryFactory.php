<?php

namespace Modules\Services\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Services\Models\ServiceCategory;
use Modules\Services\Models\ServiceCategoryTranslation;

/** @extends Factory<ServiceCategory> */
class ServiceCategoryFactory extends Factory
{
    protected $model = ServiceCategory::class;

    /** @return array{name: string} */
    public function configure(): static
    {
        return $this->afterMaking(function (ServiceCategory $record): void {
            $fields = array_intersect_key($record->getAttributes(), array_flip(['name', 'extra_fields']));
            foreach (array_keys($fields) as $field) {
                $record->offsetUnset($field);
            }
            $record->setRelation('factoryTranslation', new ServiceCategoryTranslation($fields));
        })->afterCreating(function (ServiceCategory $record): void {
            $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
            $record->translations()->create(['lang_id' => $language->id, ...$record->getRelation('factoryTranslation')->toArray()]);
            $record->unsetRelation('factoryTranslation');
            $record->load('translations.language');
        });
    }

    public function definition(): array
    {
        return ['name' => fake()->words(2, true), 'extra_fields' => null];
    }
}
