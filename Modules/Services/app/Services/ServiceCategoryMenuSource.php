<?php

namespace Modules\Services\Services;

use App\Models\Settings\Language;
use Modules\Menus\Interfaces\LocalizedMenuSource;
use Modules\Menus\Interfaces\MenuSource;
use Modules\Services\Models\ServiceCategory;

class ServiceCategoryMenuSource implements LocalizedMenuSource, MenuSource
{
    public function label(): string
    {
        return 'Hizmet kategorileri (bağlantısız grup)';
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

        return ServiceCategory::query()->where('is_active', true)
            ->whereHas('translations', fn ($query) => $query->where('lang_id', $language->id))
            ->with(['translations' => fn ($query) => $query->where('lang_id', $language->id)])
            ->orderBy('sort_order')->orderBy('id')->get()
            ->each(fn (ServiceCategory $category) => $category->setRelation('selectedTranslation', $category->translations->first()))
            ->map(fn (ServiceCategory $category): array => ['value' => (string) $category->id, 'label' => $category->name.' (grup başlığı)'])
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
            return ['label' => 'Hizmet kategorisi', 'url' => null, 'error' => 'Seçilen dil etkin değil.'];
        }
        request()->attributes->set('services_language', $language->code);
        $category = ServiceCategory::query()->with(['translations' => fn ($query) => $query->where('lang_id', $language->id)])->find($id);
        if ($category === null) {
            return null;
        }
        $translation = $category->translations->first();
        if ($translation === null) {
            return ['label' => 'Hizmet kategorisi', 'url' => null, 'error' => 'Kategori seçilen dilde çevrilmemiş.'];
        }
        $category->setRelation('selectedTranslation', $translation);

        return ['label' => $category->name, 'url' => null, 'error' => $category->is_active ? null : 'Hizmet kategorisi devre dışı.'];
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
