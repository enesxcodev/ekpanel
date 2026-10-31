<?php

namespace Modules\Sector\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Sector\Models\Sector;
use Nwidart\Modules\Facades\Module;

class SectorFrontendService
{
    public function __construct(private SectorLanguageService $languages) {}

    /** @return Collection<int, Sector> */
    public function getAll(?string $languageCode = null): Collection
    {
        return $this->visibleQuery($languageCode)?->get()->each(fn (Sector $sector) => $this->localize($sector)) ?? new Collection;
    }

    public function getFirst(?string $languageCode = null): ?Sector
    {
        $sector = $this->visibleQuery($languageCode)?->first();

        return $sector ? $this->localize($sector) : null;
    }

    public function find(int $id, ?string $languageCode = null): ?Sector
    {
        $sector = $this->visibleQuery($languageCode)?->whereKey($id)->first();

        return $sector ? $this->localize($sector) : null;
    }

    private function localize(Sector $sector): Sector
    {
        $sector->setRelation('selectedTranslation', $sector->getRelation('translations')->first());

        return $sector;
    }

    /** @return Builder<Sector>|null */
    private function visibleQuery(?string $languageCode): ?Builder
    {
        if (! Module::find('Sector')?->isEnabled() || ! Schema::hasTable('sectors') || ! Schema::hasTable('languages') || ! Schema::hasTable('sector_translations')) {
            return null;
        }
        $language = $this->languages->forFrontend($languageCode);
        if ($language === null) {
            return null;
        }

        return Sector::query()->with(['imageMedia', 'translations' => fn ($query) => $query->where('lang_id', $language->id)])
            ->where('is_active', true)->whereHas('translations', fn ($query) => $query->where('lang_id', $language->id))
            ->orderBy('sort_order')->orderBy('id');
    }
}
