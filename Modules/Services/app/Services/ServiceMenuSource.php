<?php

namespace Modules\Services\Services;

use App\Models\Settings\Language;
use Modules\Menus\Interfaces\LocalizedMenuSource;
use Modules\Menus\Interfaces\MenuSource;
use Modules\Services\Models\Service;

class ServiceMenuSource implements LocalizedMenuSource, MenuSource
{
    public function label(): string
    {
        return 'Hizmetler (genel bağlantı yok)';
    }

    /** @return list<array{value: string, label: string}> */
    public function options(): array
    {
        return $this->optionsForLanguage($this->currentLanguageCode());
    }

    /** @return list<array{value: string, label: string}> */
    public function optionsForLanguage(string $languageCode): array
    {
        $language = $this->language($languageCode);
        if ($language === null) {
            return [];
        }
        request()->attributes->set('services_language', $language->code);

        return Service::query()->where('is_active', true)
            ->whereHas('translations', fn ($query) => $query->where('lang_id', $language->id))
            ->with(['translations' => fn ($query) => $query->where('lang_id', $language->id)])
            ->orderBy('sort_order')->orderBy('id')->get()
            ->each(fn (Service $service) => $service->setRelation('selectedTranslation', $service->translations->first()))
            ->map(fn (Service $service): array => ['value' => (string) $service->id, 'label' => $service->title.' (genel bağlantı yok)'])
            ->all();
    }

    /** @return array{label: string, url: ?string, error: ?string}|null */
    public function find(string $id): ?array
    {
        return $this->findForLanguage($id, $this->currentLanguageCode());
    }

    /** @return array{label: string, url: ?string, error: ?string}|null */
    public function findForLanguage(string $id, string $languageCode): ?array
    {
        $language = $this->language($languageCode);
        if ($language === null) {
            return ['label' => 'Hizmet', 'url' => null, 'error' => 'Seçilen dil etkin değil.'];
        }
        request()->attributes->set('services_language', $language->code);
        $service = Service::query()->with(['translations' => fn ($query) => $query->where('lang_id', $language->id)])->find($id);
        if ($service === null) {
            return null;
        }
        $translation = $service->translations->first();
        if ($translation === null) {
            return ['label' => 'Hizmet', 'url' => null, 'error' => 'Hizmet seçilen dilde çevrilmemiş.'];
        }
        $service->setRelation('selectedTranslation', $translation);

        return ['label' => $service->title, 'url' => null, 'error' => $service->is_active
            ? 'Hizmetler modülünde herkese açık detay route tanımlı değil. Özel URL veya bölüm bağlantısı kullanın.'
            : 'Hizmet devre dışı.'];
    }

    public function supportsChildren(): bool
    {
        return false;
    }

    /** @return list<array{label: string, url: string, new_tab: bool, children: array}> */
    public function children(string $id): array
    {
        return [];
    }

    /** @return list<array{label: string, url: string, new_tab: bool, children: array}> */
    public function childrenForLanguage(string $id, string $languageCode): array
    {
        return [];
    }

    private function currentLanguageCode(): string
    {
        return (string) (request()->route('lang') ?? app(ServicesLanguageService::class)->forFrontend()?->code ?? ServicesLanguageService::PrimaryCode);
    }

    private function language(string $code): ?Language
    {
        return app(ServicesLanguageService::class)->forFrontend($code);
    }
}
