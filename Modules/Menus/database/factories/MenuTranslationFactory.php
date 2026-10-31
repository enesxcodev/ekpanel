<?php

namespace Modules\Menus\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Menus\Models\Menu;
use Modules\Menus\Models\MenuTranslation;

/** @extends Factory<MenuTranslation> */
class MenuTranslationFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = MenuTranslation::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        return [
            'menu_id' => Menu::factory(),
            'lang_id' => Language::query()->where('code', 'tr')->value('id'),
            'name' => fake()->words(2, true),
        ];
    }
}
