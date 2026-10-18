<?php

namespace App\Services\Frontend;

use App\Services\Settings\SettingsService;
use Illuminate\Support\Facades\Schema;
use Modules\Menus\Services\NavigationService;
use Modules\Services\Models\Service;
use Modules\Slider\Models\Slider;
use Nwidart\Modules\Facades\Module;

class HomepageViewDataBuilder
{
    public function __construct(private SettingsService $settings, private NavigationService $navigation) {}

    /** @return array<string, mixed> */
    public function build(): array
    {
        $whatsAppNumber = preg_replace('/\D+/', '', (string) $this->settings->get('contact.whatsapp', ''));
        $navigationItems = Module::isEnabled('Menus') && Schema::hasTable('menus')
            ? $this->navigation->tree((string) config('menus.main_menu', 'main'))
            : [];
        $banner = Module::isEnabled('Slider') && Schema::hasTable('sliders')
            ? Slider::query()->with('imageMedia')->where('is_active', true)->first()
            : null;
        $services = Module::isEnabled('Services') && Schema::hasTable('services')
            ? Service::query()->with('category')->where('is_active', true)->orderBy('sort_order')->orderBy('id')->get()
            : collect();
        $featuredServices = $services->filter(fn (Service $service): bool => $service->category === null
            ? true
            : $service->category->is_active && ($service->category->extra_fields['homepage_display'] ?? 'cards') === 'cards')->values();
        $otherServices = $services->filter(fn (Service $service): bool => $service->category !== null
            && $service->category->is_active
            && ($service->category->extra_fields['homepage_display'] ?? 'cards') === 'compact')->values();
        $address = trim((string) $this->settings->get('contact.address', ''));
        $savedDirectionsUrl = trim((string) ($banner?->extra_fields['directions_url'] ?? ''));
        $directionsUrl = $this->safeLink($savedDirectionsUrl)
            ?? $this->safeLink(trim((string) $this->settings->get('contact.map_iframe', '')))
            ?? ($address !== ''
                ? 'https://www.google.com/maps/search/?api=1&query='.rawurlencode($address)
                : 'https://www.google.com/maps/search/?api=1&query=Cihan+Caner+Auto+Service');

        $highlights = collect([1, 2, 3])->map(fn (int $number): array => [
            'icon' => $this->settings->get('home_highlights.item_'.$number.'_icon', ['search', 'wrench', 'message'][$number - 1]),
            'title' => $this->settings->get('home_highlights.item_'.$number.'_title', ['Arıza Tespiti', 'Bakım ve Onarım', 'Kolay İletişim'][$number - 1]),
            'description' => $this->settings->get('home_highlights.item_'.$number.'_description', ['Profesyonel cihazlarla doğru tespit.', 'Güvenilir ve titiz hizmet.', 'Randevu ve bilgi için her zaman ulaşabilirsiniz.'][$number - 1]),
        ])->all();

        return [
            'navigationItems' => $navigationItems,
            'whatsAppUrl' => $whatsAppNumber === '' ? null : 'https://wa.me/'.$whatsAppNumber,
            'banner' => $banner,
            'featuredServices' => $featuredServices,
            'otherServices' => $otherServices,
            'highlights' => $highlights,
            'brandLogoUrl' => $this->settings->mediaUrl('site.logo'),
            'footerLogoUrl' => $this->settings->mediaUrl('site.footer_logo') ?? $this->settings->mediaUrl('site.logo'),
            'instagramUrl' => $this->safeLink((string) $this->settings->get('social.instagram', '')),
            'companyName' => $this->settings->get('site.company_name', 'Cihan Caner Auto Service'),
            'siteTitle' => $this->settings->get('site.meta_title') ?: $this->settings->get('site.title', 'Cihan Caner Auto Service | Güvenilir Oto Servis'),
            'siteDescription' => $this->settings->get('site.meta_description') ?: $this->settings->get('site.description', 'Bakım, arıza tespiti ve onarım hizmetleri tek noktada.'),
            'siteKeywords' => $this->settings->get('site.meta_keywords'),
            'workingHours' => $this->settings->get('contact.working_hours', ''),
            'address' => $address,
            'directionsUrl' => $directionsUrl,
            'servicesCopy' => [
                'eyebrow' => $this->settings->get('homepage_services.eyebrow', 'HİZMETLERİMİZ'),
                'title' => $this->settings->get('homepage_services.title', 'Aracınız İçin Profesyonel Çözümler'),
                'description' => $this->settings->get('homepage_services.description', 'Tüm marka ve model araçlar için bakım, onarım ve arıza tespit hizmetleri sunuyoruz.'),
                'button_text' => $this->settings->get('homepage_services.button_text', 'Tüm Hizmetlerimiz'),
                'button_url' => $this->safeLink((string) $this->settings->get('homepage_services.button_url', '')) ?? '#service-cards',
                'other_title' => $this->settings->get('homepage_services.other_title', 'Diğer Hizmetlerimiz'),
            ],
            'aboutCopy' => [
                'eyebrow' => $this->settings->get('about.eyebrow', 'HAKKIMIZDA'),
                'title' => $this->settings->get('about.title', $this->settings->get('site.company_name', 'Cihan Caner Auto Service')),
                'short_description' => $this->settings->get('about.short_description', 'Aracınızın bakım ve onarım ihtiyaçları için yanınızdayız.'),
                'description' => $this->settings->get('about.description', 'Deneyimli ekibimiz ve profesyonel ekipmanlarımızla tüm marka ve model araçlar için güvenilir, kaliteli ve titiz bir servis hizmeti sunuyoruz.'),
                'image_url' => $this->settings->mediaUrl('about.image'),
                'button_text' => $this->settings->get('about.button_text', 'WhatsApp ile İletişim'),
            ],
            'ctaCopy' => [
                'eyebrow' => $this->settings->get('cta.eyebrow', 'ARACINIZ BİZE EMANET'),
                'title' => $this->settings->get('cta.title', 'Güvenilir servis, uzun yol arkadaşlığı.'),
                'description' => $this->settings->get('cta.description', 'Bakım, onarım veya arıza tespiti için hemen bizimle iletişime geçin.'),
                'button_text' => $this->settings->get('cta.button_text', 'WhatsApp ile İletişim'),
            ],
        ];
    }

    private function safeLink(string $candidate): ?string
    {
        if ($candidate === '' || preg_match('/[\x00-\x20\x7f\\\\]/', $candidate)) {
            return null;
        }

        if (preg_match('/^#[A-Za-z][A-Za-z0-9_:.-]*$/', $candidate)) {
            return $candidate;
        }

        if (str_starts_with($candidate, '/') && ! str_starts_with($candidate, '//')) {
            return $candidate;
        }

        return preg_match('/^https?:\/\//i', $candidate) && filter_var($candidate, FILTER_VALIDATE_URL) !== false
            ? $candidate
            : null;
    }
}
