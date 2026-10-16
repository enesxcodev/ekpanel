<?php

namespace Database\Factories\Settings;

use App\Enums\Settings\SettingInputType;
use App\Enums\Settings\SettingValueType;
use App\Models\Settings\SettingDefinition;
use App\Models\Settings\SettingSection;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SettingDefinition> */
class SettingDefinitionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'section_id' => SettingSection::factory(), 'key' => 'custom.'.fake()->unique()->slug(2, '_'),
            'label' => fake()->words(2, true), 'input_type' => SettingInputType::Text,
            'value_type' => SettingValueType::String, 'validation' => ['required' => false, 'max' => 255],
            'options' => [], 'attributes' => [], 'default_value' => null,
            'is_sensitive' => false, 'is_system' => false, 'sort_order' => 0,
        ];
    }

    public function required(): static
    {
        return $this->state(['validation' => ['required' => true, 'max' => 255]]);
    }

    public function password(): static
    {
        return $this->state(['input_type' => SettingInputType::Password, 'is_sensitive' => true]);
    }

    public function image(): static
    {
        return $this->state([
            'input_type' => SettingInputType::Image, 'value_type' => SettingValueType::File,
            'validation' => ['required' => false, 'max_size' => 2048, 'extensions' => ['jpg', 'png', 'webp']],
        ]);
    }
}
