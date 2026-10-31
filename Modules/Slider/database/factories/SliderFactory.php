<?php

namespace Modules\Slider\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Slider\Models\Slider;
use Modules\Slider\Models\SliderTranslation;

class SliderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Slider::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return ['image' => 'slider/example.png', 'is_active' => true];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Slider $slider): void {
            $language = Language::query()->updateOrCreate(['code' => 'tr'], ['is_active' => true]);
            SliderTranslation::query()->create(['slider_id' => $slider->id, 'lang_id' => $language->id, 'title' => fake()->sentence(3), 'extra_fields' => []]);
        });
    }
}
