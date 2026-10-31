<?php

namespace Modules\Services\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Services\Models\Service;
use Modules\Services\Models\ServiceTranslation;

/** @extends Factory<Service> */
class ServiceFactory extends Factory
{
    protected $model = Service::class;

    public function configure(): static
    {
        return $this->afterMaking(function (Service $record): void {
            $fields = array_intersect_key($record->getAttributes(), array_flip(['title', 'slug', 'short_description', 'content', 'meta_title', 'meta_keywords', 'meta_description', 'extra_fields']));
            foreach (array_keys($fields) as $field) {
                $record->offsetUnset($field);
            }
            $record->setRelation('factoryTranslation', new ServiceTranslation($fields));
        })->afterCreating(function (Service $record): void {
            $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
            $record->translations()->create(['lang_id' => $language->id, ...$record->getRelation('factoryTranslation')->toArray()]);
            $record->unsetRelation('factoryTranslation');
            $record->load('translations.language');
        });
    }

    public function definition(): array
    {
        return ['title' => fake()->words(3, true), 'service_category_id' => null, 'image' => null, 'icon' => null, 'is_active' => true, 'extra_fields' => []];
    }
}
