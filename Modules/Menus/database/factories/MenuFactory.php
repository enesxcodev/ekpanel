<?php

namespace Modules\Menus\Database\Factories;

use App\Models\Settings\Language;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Menus\Models\Menu;
use Modules\Menus\Services\MenusLanguageService;

/** @extends Factory<Menu> */
class MenuFactory extends Factory
{
    protected $model = Menu::class;

    public function definition(): array
    {
        return ['name' => fake()->words(2, true), 'key' => fake()->unique()->slug(2), 'is_active' => true];
    }

    public function configure(): static
    {
        return $this->afterCreating(function (Menu $menu): void {
            $languageId = Language::query()->where('code', MenusLanguageService::PrimaryCode)->where('is_active', true)->value('id');
            if ($languageId === null) {
                return;
            }

            $menu->translations()->create(['lang_id' => $languageId, 'name' => $menu->name]);
        });
    }

    public function hidden(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
