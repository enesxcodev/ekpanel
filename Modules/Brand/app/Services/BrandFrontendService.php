<?php

namespace Modules\Brand\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Brand\Models\Brand;
use Nwidart\Modules\Facades\Module;

class BrandFrontendService
{
    /** @return Collection<int, Brand> */
    public function getAll(): Collection
    {
        return $this->visibleQuery()?->get() ?? new Collection;
    }

    public function getFirst(): ?Brand
    {
        return $this->visibleQuery()?->first();
    }

    public function find(int $id): ?Brand
    {
        return $this->visibleQuery()?->whereKey($id)->first();
    }

    /** @return Builder<Brand>|null */
    private function visibleQuery(): ?Builder
    {
        if (! Module::find('Brand')?->isEnabled() || ! Schema::hasTable('brands')) {
            return null;
        }

        $query = Brand::query()->with(['imageMedia'])->where('is_active', true)->orderBy('sort_order')->orderBy('id');

        return $query;
    }
}
