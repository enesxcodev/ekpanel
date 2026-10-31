<?php

namespace Modules\Store\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Store\Models\StoreProduct;
use Modules\Store\Models\StoreProductTranslation;

class StoreProductFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = StoreProduct::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return ['store_category_id' => null, 'sku' => fake()->unique()->bothify('SKU-#####'), 'price_minor' => 10000, 'currency' => 'TRY', 'tracks_stock' => true, 'stock_quantity' => 10, 'low_stock_threshold' => 0, 'allow_backorders' => false, 'is_active' => true, 'sort_order' => 0];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (StoreProduct $product): void {
            $language = Language::query()->firstOrCreate(['code' => 'tr'], ['is_active' => true]);
            $title = fake()->unique()->words(3, true);
            StoreProductTranslation::query()->create(['store_product_id' => $product->id, 'lang_id' => $language->id, 'title' => $title, 'slug' => 'urun-'.$product->id.'-'.str($title)->slug(), 'extra_fields' => []]);
        });
    }
}
