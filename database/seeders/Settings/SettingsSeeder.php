<?php

namespace Database\Seeders\Settings;

use App\Enums\Settings\SettingInputType;
use App\Models\Settings\SettingDefinition;
use App\Models\Settings\SettingSection;
use App\Services\Settings\SettingConfigurationService;
use App\Services\Settings\SettingsService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;

class SettingsSeeder extends Seeder
{
    public function run(SettingConfigurationService $configuration, SettingsService $settings): void
    {
        $seoSectionExists = SettingSection::query()->where('key', 'seo')->exists();
        $definitions = [
            ['section' => 'site', 'key' => 'site.title', 'label' => 'Site Başlık', 'input_type' => 'text', 'validation' => ['required' => true, 'max' => 150], 'default_value' => config('app.name')],
            ['section' => 'site', 'key' => 'site.company_name', 'label' => 'Firma Adı', 'input_type' => 'text', 'validation' => ['required' => true, 'max' => 150], 'default_value' => 'EkAgent'],
            ['section' => 'site', 'key' => 'site.description', 'label' => 'Kısa Slogan', 'input_type' => 'textarea', 'validation' => ['required' => false, 'max' => 1000], 'attributes' => ['rows' => 4]],
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
            ['section' => 'seo', 'key' => 'seo.og_title', 'label' => 'Paylaşım başlığı', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 255], 'description' => 'Boşsa sayfanın SEO başlığı kullanılır.'],
            ['section' => 'seo', 'key' => 'seo.og_description', 'label' => 'Paylaşım açıklaması', 'input_type' => 'textarea', 'validation' => ['required' => false, 'max' => 1000], 'attributes' => ['rows' => 3]],
            ['section' => 'seo', 'key' => 'seo.og_image', 'label' => 'Paylaşım görseli', 'input_type' => 'image', 'validation' => ['required' => false, 'max_size' => 2048, 'extensions' => ['jpg', 'jpeg', 'png', 'webp']]],
            ['section' => 'seo', 'key' => 'seo.og_image_alt', 'label' => 'Paylaşım görseli alternatif metni', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 255]],
            ['section' => 'seo', 'key' => 'seo.og_site_name', 'label' => 'Paylaşımda görünen site adı', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 150]],
            ['section' => 'seo', 'key' => 'seo.twitter_card', 'label' => 'X kart türü', 'input_type' => 'select', 'default_value' => 'summary_large_image', 'options' => [['label' => 'Standart', 'value' => 'summary'], ['label' => 'Büyük görselli', 'value' => 'summary_large_image']]],
            ['section' => 'seo', 'key' => 'seo.twitter_site', 'label' => 'X hesap kullanıcı adı', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 16], 'description' => 'Örnek: @firmaadi'],
            ['section' => 'seo', 'key' => 'seo.twitter_title', 'label' => 'X kart başlığı', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 255]],
            ['section' => 'seo', 'key' => 'seo.twitter_description', 'label' => 'X kart açıklaması', 'input_type' => 'textarea', 'validation' => ['required' => false, 'max' => 1000], 'attributes' => ['rows' => 3]],
            ['section' => 'seo', 'key' => 'seo.twitter_image', 'label' => 'X kart görseli', 'input_type' => 'image', 'validation' => ['required' => false, 'max_size' => 2048, 'extensions' => ['jpg', 'jpeg', 'png', 'webp']]],
            ['section' => 'seo', 'key' => 'seo.twitter_image_alt', 'label' => 'X görseli alternatif metni', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 255]],
            ['section' => 'seo', 'key' => 'seo.legal_name', 'label' => 'Resmî ticari unvan', 'input_type' => 'text', 'validation' => ['required' => false, 'max' => 255], 'description' => 'Firma adı, logo, adres, telefon, e-posta ve sosyal profiller mevcut ayarlardan alınır.'],
        ];

        $groups = [
            'site' => [
                ['label' => 'Firma bilgileri', 'columns' => 2, 'fields' => ['site.title', 'site.company_name', 'site.description']],
                ['label' => 'Logolar ve site simgesi', 'columns' => 2, 'fields' => ['site.logo', 'site.footer_logo', 'site.favicon']],
            ],
            'seo' => [
                ['label' => 'Temel SEO', 'columns' => 2, 'fields' => ['site.meta_title', 'site.meta_keywords', 'site.meta_description']],
                ['label' => 'Open Graph', 'columns' => 2, 'fields' => ['seo.og_title', 'seo.og_site_name', 'seo.og_description', 'seo.og_image', 'seo.og_image_alt']],
                ['label' => 'X Kartı', 'columns' => 2, 'fields' => ['seo.twitter_card', 'seo.twitter_site', 'seo.twitter_title', 'seo.twitter_description', 'seo.twitter_image', 'seo.twitter_image_alt']],
                ['label' => 'JSON-LD', 'columns' => 1, 'fields' => ['seo.legal_name']],
            ],
            'contact' => [
                ['label' => 'İletişim bilgileri', 'columns' => 2, 'fields' => ['contact.email', 'contact.phone', 'contact.whatsapp', 'contact.working_hours', 'contact.address', 'contact.map_iframe']],
            ],
            'social' => [
                ['label' => 'Sosyal medya', 'columns' => 2, 'fields' => ['social.linkedin', 'social.facebook', 'social.twitter', 'social.instagram', 'social.pinterest', 'social.youtube']],
            ],
            'scripts' => [
                ['label' => 'Script kodları', 'columns' => 1, 'fields' => ['scripts.head', 'scripts.body']],
            ],
            'mail' => [
                ['label' => 'SMTP bağlantısı', 'columns' => 2, 'fields' => ['mail.enabled', 'mail.host', 'mail.port', 'mail.encryption', 'mail.username', 'mail.password']],
                ['label' => 'Gönderen bilgileri', 'columns' => 2, 'fields' => ['mail.from_address', 'mail.from_name']],
            ],
        ];
        $layoutByKey = [];
        foreach ($groups as $sectionKey => $sectionGroups) {
            foreach ($sectionGroups as $groupOrder => $group) {
                foreach ($group['fields'] as $fieldOrder => $key) {
                    $layoutByKey[$key] = [
                        'section' => $sectionKey,
                        'sort_order' => ($groupOrder * 1000) + (($fieldOrder + 1) * 10),
                        'attributes' => ['group' => $group['label'], 'group_order' => ($groupOrder + 1) * 10, 'columns' => $group['columns']],
                    ];
                }
            }
        }
        foreach ($definitions as &$definition) {
            $layout = $layoutByKey[$definition['key']] ?? null;
            if ($layout !== null) {
                $definition['section'] = $layout['section'];
                $definition['sort_order'] = $layout['sort_order'];
                $definition['attributes'] = array_replace($definition['attributes'] ?? [], $layout['attributes']);
                if ($definition['input_type'] === 'textarea') {
                    $definition['attributes']['width'] = 12;
                }
            }
        }
        unset($definition);

        $sections = [
            'site' => ['label' => 'Genel', 'sort_order' => 10],
            'seo' => ['label' => 'SEO', 'sort_order' => 15],
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
                $existing = SettingDefinition::query()->where('key', $attributes['key'])->first();
                if ($existing !== null && ! $existing->is_system) {
                    continue;
                }
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
                    'sort_order' => $attributes['sort_order'] ?? ($order + 1) * 10,
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
        if (! $seoSectionExists) {
            foreach (['view', 'update'] as $ability) {
                $source = Permission::query()->where('guard_name', 'admin')->where('name', 'settings.sections.site.'.$ability)->first();
                $target = Permission::findByName('settings.sections.seo.'.$ability, 'admin');
                foreach ($source?->roles ?? [] as $role) {
                    $role->givePermissionTo($target);
                }
                foreach ($source?->users ?? [] as $admin) {
                    $admin->givePermissionTo($target);
                }
            }
        }
    }
}
