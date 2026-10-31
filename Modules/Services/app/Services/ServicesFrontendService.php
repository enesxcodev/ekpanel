<?php

namespace Modules\Services\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Services\Models\Service;
use Nwidart\Modules\Facades\Module;

class ServicesFrontendService
{
    /** @return Collection<int, Service> */
    public function getAll(?int $categoryId = null, ?string $languageCode = null): Collection
    {
        return $this->visibleQuery($categoryId, $languageCode)?->get() ?? new Collection;
    }

    public function getFirst(?int $categoryId = null, ?string $languageCode = null): ?Service
    {
        return $this->visibleQuery($categoryId, $languageCode)?->first();
    }

    public function find(int $id, ?string $languageCode = null): ?Service
    {
        return $this->visibleQuery(null, $languageCode)?->whereKey($id)->first();
    }

    /** @return Builder<Service>|null */
    private function visibleQuery(?int $categoryId = null, ?string $languageCode = null): ?Builder
    {
        if (! Module::find('Services')?->isEnabled() || ! Schema::hasTable('services') || ! Schema::hasTable('service_categories') || ! Schema::hasTable('languages') || ! Schema::hasTable('service_translations') || ! Schema::hasTable('service_category_translations')) {
            return null;
        }

        $language = app(ServicesLanguageService::class)->forFrontend($languageCode);
        if ($language === null) {
            return null;
        }
        $query = Service::query()->whereHas('translations', fn ($query) => $query->where('lang_id', $language->id))->with(['translations' => fn ($query) => $query->where('lang_id', $language->id), 'category.translations' => fn ($query) => $query->where('lang_id', $language->id), 'imageMedia'])->where('is_active', true)->orderBy('sort_order')->orderBy('id');

        $query->where(fn (Builder $query): Builder => $query->whereNull('service_category_id')
            ->orWhereHas('category', fn (Builder $category): Builder => $category->where('is_active', true)->whereHas('translations', fn ($query) => $query->where('lang_id', $language->id))));

        if ($categoryId !== null) {
            $query->where('service_category_id', $categoryId);
        }

        return $query->afterQuery(function (Collection $services): Collection {
            foreach ($services as $service) {
                $service->setRelation('selectedTranslation', $service->translations->first());
                if ($service->category !== null) {
                    $service->category->setRelation('selectedTranslation', $service->category->translations->first());
                }
            }

            return $services;
        });
    }
}
