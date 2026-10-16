<?php

namespace Modules\Menus\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Menus\Models\Menu;
use Modules\Menus\Models\MenuItem;

/** @extends Factory<MenuItem> */
class MenuItemFactory extends Factory
{
    protected $model = MenuItem::class;

    public function definition(): array
    {
        return ['menu_id' => Menu::factory(), 'label' => fake()->words(2, true), 'target_type' => 'url', 'url' => '/iletisim', 'is_active' => true, 'new_tab' => false, 'dynamic_children' => false, 'sort_order' => 0];
    }

    public function group(): static
    {
        return $this->state(fn (): array => ['target_type' => 'group', 'url' => null]);
    }

    public function hidden(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
