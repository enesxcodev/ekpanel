<?php

namespace App\Services\Frontend;

use App\Services\Settings\SettingsService;
use Illuminate\Database\Eloquent\Model;
use Modules\Menus\Services\MenusFrontendService;
use Modules\Services\Services\ServicesFrontendService;
use Modules\Slider\Services\SliderFrontendService;

class HomepageViewDataBuilder
{
    public function __construct(private SettingsService $settings, private ModuleFrontendServiceResolver $modules) {}

    /** @return array<string, mixed> */
    public function build(): array
    {
        $whatsAppNumber = preg_replace('/\D+/', '', (string) $this->settings->get('contact.whatsapp', ''));
        $navigationItems = $this->modules->resolve(MenusFrontendService::class)?->getTree((string) config('menus.main_menu', 'main')) ?? [];
        $banner = $this->modules->resolve(SliderFrontendService::class)?->getFirst();
        $services = $this->modules->resolve(ServicesFrontendService::class)?->getAll() ?? collect();
        $featuredServices = $services->filter(fn (Model $service): bool => $service->category === null
            ? true
            : ($service->category->extra_fields['homepage_display'] ?? 'cards') === 'cards')->values();
        $otherServices = $services->filter(fn (Model $service): bool => $service->category !== null
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

        $data = [
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

        $companyName = $data['companyName'];
        $whatsAppUrl = $data['whatsAppUrl'];
        $garageImage = $banner?->image_url ?? asset('images/themes/general/route66-sanayi.jpg');
        $aboutImage = $data['aboutCopy']['image_url'] ?? $garageImage;
        $heroTitle = $banner?->title ?: 'Aracınız için';
        $heroHighlight = $banner?->extra_fields['highlighted_title'] ?? 'güvenilir servis';
        $heroSubtitle = $banner?->subtitle ?: 'Bakım, arıza tespiti ve onarım hizmetleri tek noktada.';
        $heroButtonText = $banner?->extra_fields['button_text'] ?? 'WhatsApp ile İletişim';
        $heroEyebrow = $banner?->extra_fields['eyebrow'] ?? 'GÜVENİLİR SERVİS, UZMAN ÇÖZÜM';
        $heroDirectionsText = $banner?->extra_fields['directions_button_text'] ?? 'Yol Tarifi Al';

        $contact = array_intersect_key($data, array_flip(['workingHours', 'address', 'directionsUrl', 'whatsAppUrl'])) + compact('heroDirectionsText');
        $header = array_intersect_key($data, array_flip(['navigationItems', 'brandLogoUrl', 'companyName', 'whatsAppUrl']));
        $footer = array_intersect_key($data, array_flip(['navigationItems', 'footerLogoUrl', 'companyName', 'instagramUrl']));

        return [
            'layout' => array_intersect_key($data, array_flip(['siteTitle', 'siteDescription', 'siteKeywords', 'whatsAppUrl'])) + [
                'header' => $header,
                'footer' => $footer,
                'headCode' => (string) $this->settings->get('scripts.head', ''),
                'bodyCode' => (string) $this->settings->get('scripts.body', ''),
            ],
            'hero' => compact('garageImage', 'companyName', 'heroTitle', 'heroHighlight', 'heroSubtitle', 'heroButtonText', 'heroEyebrow', 'heroDirectionsText', 'whatsAppUrl', 'directionsUrl', 'highlights'),
            'services' => array_intersect_key($data, array_flip(['servicesCopy', 'featuredServices', 'otherServices'])),
            'about' => ['aboutCopy' => $data['aboutCopy']] + compact('aboutImage', 'companyName', 'whatsAppUrl', 'contact'),
            'cta' => ['ctaCopy' => $data['ctaCopy'], 'whatsAppUrl' => $whatsAppUrl],
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
