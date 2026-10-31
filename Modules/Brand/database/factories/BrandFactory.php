<?php

namespace Modules\Brand\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Schema;
use Modules\Brand\Models\Brand;

/** @extends Factory<Brand> */
class BrandFactory extends Factory
{
    protected $model = Brand::class;

    public function definition(): array
    {
        return ['image' => 'brand/images/example.png', 'link' => null, 'sort_order' => null, 'is_active' => true];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Brand $brand): void {
            $language = Language::query()->where('code', 'tr')->first();
            if ($language && Schema::hasTable('brand_translations')) {
                $brand->translations()->firstOrCreate(['lang_id' => $language->id], ['name' => fake()->company(), 'short_description' => null, 'extra_fields' => []]);
            }
        });
    }
}
