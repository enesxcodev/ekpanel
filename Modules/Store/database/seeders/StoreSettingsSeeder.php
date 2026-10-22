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
            ['key' => 'store.shipping_price', 'label' => 'Sabit teslimat ücreti (TL)', 'input_type' => SettingInputType::Text, 'default_value' => '0.00', 'validation' => ['required' => true, 'regex' => '/^\d{1,9}([.,]\d{1,2})?$/D'], 'sort_order' => 50],
            ['key' => 'store.free_shipping_threshold', 'label' => 'Ücretsiz teslimat eşiği (TL, boşsa kapalı)', 'input_type' => SettingInputType::Text, 'default_value' => null, 'validation' => ['required' => false, 'regex' => '/^\d{1,9}([.,]\d{1,2})?$/D'], 'sort_order' => 60],
            ['key' => 'store.pickup_enabled', 'label' => 'Mağazadan teslim alma', 'input_type' => SettingInputType::Boolean, 'default_value' => false, 'validation' => ['required' => false], 'sort_order' => 70],
            ['key' => 'store.manual_payment_instructions', 'label' => 'Manuel ödeme açıklaması', 'input_type' => SettingInputType::Textarea, 'default_value' => '', 'validation' => ['required' => false, 'max' => 2000], 'sort_order' => 80],
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
