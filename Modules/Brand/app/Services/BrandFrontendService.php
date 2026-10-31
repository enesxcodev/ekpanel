<?php

namespace Modules\Brand\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Brand\Models\Brand;
use Nwidart\Modules\Facades\Module;

class BrandFrontendService
{
    public function __construct(private BrandLanguageService $languages) {}

    /** @return Collection<int, Brand> */
    public function getAll(?string $languageCode = null): Collection
    {
        return $this->visibleQuery($languageCode)?->get() ?? new Collection;
    }

    public function getFirst(?string $languageCode = null): ?Brand
    {
        return $this->visibleQuery($languageCode)?->first();
    }

    public function find(int $id, ?string $languageCode = null): ?Brand
    {
        return $this->visibleQuery($languageCode)?->whereKey($id)->first();
    }

    /** @return Builder<Brand>|null */
    private function visibleQuery(?string $languageCode = null): ?Builder
    {
        if (! Module::find('Brand')?->isEnabled() || ! Schema::hasTable('brands') || ! Schema::hasTable('brand_translations') || ! Schema::hasTable('languages')) {
            return null;
        }
        $language = $this->languages->forFrontend($languageCode);
        if ($language === null) {
            return null;
        }
        request()->attributes->set('brand_language_id', $language->id);

        return Brand::query()->with(['imageMedia', 'selectedTranslation'])
            ->where('is_active', true)->whereHas('translations', fn ($q) => $q->where('lang_id', $language->id))->orderBy('sort_order')->orderBy('id');
    }
}
