<?php

namespace Modules\Store\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Store\Models\StoreCategory;
use Modules\Store\Models\StoreCategoryTranslation;

class StoreCategoryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = StoreCategory::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return ['sort_order' => 0, 'is_active' => true];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (StoreCategory $category): void {
            $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
            $name = fake()->unique()->words(2, true);
            StoreCategoryTranslation::query()->create(['store_category_id' => $category->id, 'lang_id' => $language->id, 'name' => $name, 'slug' => 'kategori-'.$category->id.'-'.str($name)->slug(), 'extra_fields' => []]);
        });
    }
}
