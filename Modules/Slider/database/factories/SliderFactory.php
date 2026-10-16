<?php

namespace Modules\Slider\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Slider\Models\Slider;

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
        return ['title' => fake()->sentence(3), 'subtitle' => null, 'image' => 'slider/example.png', 'extra_fields' => []];
    }
}
