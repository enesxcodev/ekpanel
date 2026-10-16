<?php

namespace Database\Factories\Settings;

use App\Models\Settings\SettingSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SettingSection> */
class SettingSectionFactory extends Factory
{
    public function definition(): array
    {
        return ['key' => fake()->unique()->slug(2, '_'), 'label' => fake()->words(2, true), 'sort_order' => 0];
    }
}
