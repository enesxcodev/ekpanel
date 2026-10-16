<?php

namespace Modules\Menus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Menus\Models\Menu;

/** @extends Factory<Menu> */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return ['name' => fake()->words(2, true), 'key' => fake()->unique()->slug(2), 'is_active' => true];
    }

    public function hidden(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
