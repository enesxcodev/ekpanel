<?php

namespace Modules\Galeriler\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Galeriler\Models\GalerilerCategory;
use Modules\Galeriler\Models\GalerilerItem;

/** @extends Factory<GalerilerItem> */
class GalerilerItemFactory extends Factory
{
    protected $model = GalerilerItem::class;

    public function definition(): array
    {
        return ['galeriler_category_id' => GalerilerCategory::factory(), 'type' => 'video', 'title' => fake()->sentence(3), 'description' => null, 'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'sort_order' => 0, 'extra_fields' => []];
    }
}
