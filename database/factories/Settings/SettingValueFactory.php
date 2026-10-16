<?php

namespace Database\Factories\Settings;

use App\Models\Settings\SettingDefinition;
use App\Models\Settings\SettingValue;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SettingValue> */
class SettingValueFactory extends Factory
{
    public function definition(): array
    {
        return ['setting_definition_id' => SettingDefinition::factory(), 'value' => ['value' => fake()->sentence()]];
    }
}
