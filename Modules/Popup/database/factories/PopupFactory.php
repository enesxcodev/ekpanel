<?php

namespace Modules\Popup\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Popup\Models\Popup;

class PopupFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = Popup::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return ['title' => fake()->sentence(3), 'content' => 'Duyuru içeriği', 'is_active' => true, 'starts_at' => now()->subDay(), 'ends_at' => now()->addDay(), 'repeat_policy' => 'never_after_close', 'extra_fields' => []];
    }
}
