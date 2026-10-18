<?php

namespace Database\Seeders\Settings;

use App\Enums\Settings\SettingInputType;
use App\Models\Settings\SettingDefinition;
use App\Models\Settings\SettingSection;
use App\Services\Settings\SettingConfigurationService;
use App\Services\Settings\SettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SettingsSeeder extends Seeder
{
    public function run(SettingConfigurationService $configuration, SettingsService $settings): void
    {
        $definitions = [
            ['section' => 'site', 'key' => 'site.title', 'label' => 'Site Başlık (Türkçe)', 'input_type' => 'text', 'validation' => ['required' => true, 'max' => 150], 'default_value' => config('app.name')],
            ['section' => 'site', 'key' => 'site.company_name', 'label' => 'Firma Adı', 'input_type' => 'text', 'validation' => ['required' => true, 'max' => 150], 'default_value' => 'EkAgent'],
            ['section' => 'site', 'key' => 'site.description', 'label' => 'Site Açıklama (Türkçe)', 'input_type' => 'textarea', 'validation' => ['required' => false, 'max' => 1000], 'attributes' => ['rows' => 4]],
            ['section' => 'site', 'key' => 'site.meta_title', 'label' => 'Meta Başlık', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 255]],
            ['section' => 'site', 'key' => 'site.meta_keywords', 'label' => 'Meta Anahtar', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 1000]],
            ['section' => 'site', 'key' => 'site.meta_description', 'label' => 'Meta Açıklama', 'input_type' => 'textarea', 'validation' => ['required' => false, 'max' => 1000], 'attributes' => ['rows' => 4]],
            ['section' => 'site', 'key' => 'site.logo', 'label' => 'Üst Logo', 'input_type' => 'image', 'validation' => ['required' => false, 'max_size' => 2048, 'extensions' => ['jpg', 'jpeg', 'png', 'webp']]],
            ['section' => 'site', 'key' => 'site.footer_logo', 'label' => 'Alt Logo', 'input_type' => 'image', 'validation' => ['required' => false, 'max_size' => 2048, 'extensions' => ['jpg', 'jpeg', 'png', 'webp']]],
            ['section' => 'site', 'key' => 'site.favicon', 'label' => 'Favicon', 'input_type' => 'image', 'validation' => ['required' => false, 'max_size' => 1024, 'extensions' => ['jpg', 'jpeg', 'png', 'webp']]],
            ['section' => 'contact', 'key' => 'contact.email', 'label' => 'E-Posta', 'input_type' => 'email', 'validation' => ['required' => false, 'max' => 255]],
            ['section' => 'contact', 'key' => 'contact.phone', 'label' => 'Telefon Numarası', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 50]],
            ['section' => 'contact', 'key' => 'contact.whatsapp', 'label' => 'WhatsApp numarası', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 50]],
            ['section' => 'contact', 'key' => 'contact.working_hours', 'label' => 'Çalışma saatleri', 'input_type' => 'textarea', 'validation' => ['required' => false, 'max' => 1000], 'attributes' => ['rows' => 3]],
            ['section' => 'contact', 'key' => 'contact.address', 'label' => 'Adres', 'input_type' => 'textarea', 'validation' => ['required' => false, 'max' => 1000], 'attributes' => ['rows' => 3]],
            ['section' => 'contact', 'key' => 'contact.map_iframe', 'label' => 'İframe Link', 'input_type' => 'textarea', 'validation' => ['required' => false, 'max' => 5000], 'attributes' => ['rows' => 4]],
            ['section' => 'social', 'key' => 'social.linkedin', 'label' => 'LinkedIn', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 255]],
            ['section' => 'social', 'key' => 'social.facebook', 'label' => 'Facebook', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 255]],
            ['section' => 'social', 'key' => 'social.twitter', 'label' => 'Twitter', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 255]],
            ['section' => 'social', 'key' => 'social.instagram', 'label' => 'İnstagram', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 255]],
            ['section' => 'social', 'key' => 'social.pinterest', 'label' => 'Pinterest', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 255]],
            ['section' => 'social', 'key' => 'social.youtube', 'label' => 'Youtube', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 255]],
            ['section' => 'scripts', 'key' => 'scripts.head', 'label' => 'Script Kod', 'input_type' => 'textarea', 'validation' => ['required' => false, 'max' => 65535], 'attributes' => ['rows' => 8]],
            ['section' => 'scripts', 'key' => 'scripts.body', 'label' => 'Script Kod Body', 'input_type' => 'textarea', 'validation' => ['required' => false, 'max' => 65535], 'attributes' => ['rows' => 8]],
            ['section' => 'mail', 'key' => 'mail.enabled', 'label' => 'SMTP ile gönderim', 'input_type' => 'boolean', 'default_value' => false, 'description' => 'Etkinleştirildiğinde e-posta gönderiminde aşağıdaki bağlantı bilgileri kullanılır.'],
            ['section' => 'mail', 'key' => 'mail.host', 'label' => 'SMTP sunucusu', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 255]],
            ['section' => 'mail', 'key' => 'mail.port', 'label' => 'Port', 'input_type' => 'number', 'default_value' => 587, 'validation' => ['required' => false, 'min' => 1, 'max' => 65535]],
            ['section' => 'mail', 'key' => 'mail.encryption', 'label' => 'Bağlantı güvenliği', 'input_type' => 'select', 'default_value' => 'tls', 'options' => [['label' => 'STARTTLS', 'value' => 'tls'], ['label' => 'SSL / TLS', 'value' => 'ssl'], ['label' => 'Şifreleme yok', 'value' => 'none']]],
            ['section' => 'mail', 'key' => 'mail.username', 'label' => 'Kullanıcı adı', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 255]],
            ['section' => 'mail', 'key' => 'mail.password', 'label' => 'SMTP şifresi', 'input_type' => 'password', 'description' => 'Boş bırakırsanız kayıtlı şifre korunur.'],
            ['section' => 'mail', 'key' => 'mail.from_address', 'label' => 'Gönderen e-posta adresi', 'input_type' => 'email', 'validation' => ['required' => false, 'max' => 255]],
            ['section' => 'mail', 'key' => 'mail.from_name', 'label' => 'Gönderen adı', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 150]],
        ];

        $sections = [
            'site' => ['label' => 'Genel', 'sort_order' => 10],
            'contact' => ['label' => 'İletişim Bilgileri', 'sort_order' => 20],
            'social' => ['label' => 'Sosyal Medya', 'sort_order' => 30],
            'scripts' => ['label' => 'Script Kod', 'sort_order' => 40],
            'mail' => ['label' => 'E-posta', 'sort_order' => 50],
        ];
        $keys = array_column($definitions, 'key');
        $managedKeys = array_values(array_unique([
            ...$keys,
            'mail.enabled', 'mail.host', 'mail.port', 'mail.encryption', 'mail.username', 'mail.password',
            'mail.from_address', 'mail.from_name',
        ]));
        $removedKeys = [];

        DB::transaction(function () use ($definitions, $sections, $keys, $managedKeys, &$removedKeys): void {
            $sectionIds = [];
            foreach ($sections as $key => $attributes) {
                $sectionIds[$key] = SettingSection::query()->updateOrCreate(['key' => $key], $attributes)->id;
            }

            foreach ($definitions as $order => $attributes) {
                $inputType = SettingInputType::from($attributes['input_type']);
                SettingDefinition::query()->updateOrCreate(['key' => $attributes['key']], [
                    'section_id' => $sectionIds[$attributes['section']],
                    'label' => $attributes['label'],
                    'input_type' => $inputType,
                    'value_type' => $inputType->valueTypes()[0],
                    'description' => $attributes['description'] ?? null,
                    'options' => $attributes['options'] ?? [],
                    'attributes' => $attributes['attributes'] ?? [],
                    'validation' => $attributes['validation'] ?? ['required' => false],
                    'default_value' => $attributes['default_value'] ?? null,
                    'is_system' => true,
                    'is_sensitive' => $inputType === SettingInputType::Password,
                    'sort_order' => ($order + 1) * 10,
                ]);
            }

            $obsoleteDefinitions = SettingDefinition::query()
                ->where('is_system', true)
                ->whereIn('key', $managedKeys)
                ->whereNotIn('key', $keys)
                ->get();
            $removedKeys = $obsoleteDefinitions->pluck('key')->all();
            $obsoleteDefinitions->each->delete();
        });

        foreach (array_unique([...$keys, ...$removedKeys]) as $key) {
            $settings->forget($key);
        }

        $legacyMailSection = SettingSection::query()->where('key', 'mail')->first();
        if ($legacyMailSection !== null && $legacyMailSection->definitions()->doesntExist()) {
            $configuration->deleteSection($legacyMailSection);
        }

        $configuration->syncPermissions();
    }
}
