<?php

namespace Modules\Sector\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Sector\Models\Sector;
use Nwidart\Modules\Facades\Module;

class SectorFrontendService
{
    /** @return Collection<int, Sector> */
    public function getAll(): Collection
    {
        return $this->visibleQuery()?->get() ?? new Collection;
    }

    public function getFirst(): ?Sector
    {
        return $this->visibleQuery()?->first();
    }

    public function find(int $id): ?Sector
    {
        return $this->visibleQuery()?->whereKey($id)->first();
    }

    /** @return Builder<Sector>|null */
    private function visibleQuery(): ?Builder
    {
        if (! Module::find('Sector')?->isEnabled() || ! Schema::hasTable('sectors')) {
            return null;
        }

        $query = Sector::query()->with(['imageMedia'])->where('is_active', true)->orderBy('sort_order')->orderBy('id');

        return $query;
    }
}
