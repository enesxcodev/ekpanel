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
    public function getAll(?int $categoryId = null): Collection
    {
        return $this->visibleQuery($categoryId)?->get() ?? new Collection;
    }

    public function getFirst(?int $categoryId = null): ?Service
    {
        return $this->visibleQuery($categoryId)?->first();
    }

    public function find(int $id): ?Service
    {
        return $this->visibleQuery()?->whereKey($id)->first();
    }

    /** @return Builder<Service>|null */
    private function visibleQuery(?int $categoryId = null): ?Builder
    {
        if (! Module::find('Services')?->isEnabled() || ! Schema::hasTable('services') || ! Schema::hasTable('service_categories')) {
            return null;
        }

        $query = Service::query()->with(['category', 'imageMedia'])->where('is_active', true)->orderBy('sort_order')->orderBy('id');

        $query->where(fn (Builder $query): Builder => $query->whereNull('service_category_id')
            ->orWhereHas('category', fn (Builder $category): Builder => $category->where('is_active', true)));

        if ($categoryId !== null) {
            $query->where('service_category_id', $categoryId);
        }

        return $query;
    }
}
