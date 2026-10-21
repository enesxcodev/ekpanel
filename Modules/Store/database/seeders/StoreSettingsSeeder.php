<?php

namespace Modules\Store\Database\Seeders;

use App\Enums\Settings\SettingInputType;
use App\Models\Settings\SettingDefinition;
use App\Models\Settings\SettingSection;
use App\Services\Settings\SettingConfigurationService;
use App\Services\Settings\SettingsService;
use Illuminate\Database\Seeder;

class StoreSettingsSeeder extends Seeder
{
    public function run(SettingConfigurationService $configuration, SettingsService $settings): void
    {
        $section = SettingSection::query()->updateOrCreate(['key' => 'store'], ['label' => 'Mağaza', 'sort_order' => 60]);
        $definitions = [
            ['key' => 'store.currency', 'label' => 'Varsayılan para birimi', 'input_type' => SettingInputType::Select, 'default_value' => 'TRY', 'options' => [['label' => 'Türk lirası (TRY)', 'value' => 'TRY']], 'validation' => ['required' => true], 'sort_order' => 10],
            ['key' => 'store.tax_included', 'label' => 'Fiyatlara vergiyi dahil et', 'input_type' => SettingInputType::Boolean, 'default_value' => true, 'validation' => ['required' => false], 'sort_order' => 20],
            ['key' => 'store.tax_rate', 'label' => 'Vergi oranı (%)', 'input_type' => SettingInputType::Number, 'default_value' => 0, 'validation' => ['required' => true, 'min' => 0, 'max' => 100], 'sort_order' => 30],
            ['key' => 'store.guest_checkout_enabled', 'label' => 'Misafir checkout etkin', 'input_type' => SettingInputType::Boolean, 'default_value' => false, 'validation' => ['required' => false], 'sort_order' => 40],
        ];

        foreach ($definitions as $definition) {
            $type = $definition['input_type'];
            SettingDefinition::query()->updateOrCreate(['key' => $definition['key']], [
                'section_id' => $section->id,
                'label' => $definition['label'],
                'input_type' => $type,
                'value_type' => $type->valueTypes()[0],
                'options' => $definition['options'] ?? [],
                'attributes' => ['group' => 'Satış ayarları', 'group_order' => 10, 'columns' => 2],
                'validation' => $definition['validation'],
                'default_value' => $definition['default_value'],
                'is_system' => true,
                'is_sensitive' => false,
                'sort_order' => $definition['sort_order'],
            ]);
            $settings->forget($definition['key']);
        }

        $configuration->syncPermissions();
    }
}
