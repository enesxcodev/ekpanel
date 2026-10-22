<?php

namespace Modules\Branches\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Branches\Models\Branch;
use Modules\Konum\Services\KonumFrontendService;
use Nwidart\Modules\Facades\Module;

class BranchesFrontendService
{
    /** @return Collection<int, Branch> */
    public function getAll(?string $category = null, int $limit = 50): Collection
    {
        return $this->visibleQuery($category)?->limit(max(1, min(100, $limit)))->get() ?? new Collection;
    }

    public function getFirst(?string $category = null): ?Branch
    {
        return $this->visibleQuery($category)?->first();
    }

    public function find(int $id): ?Branch
    {
        return $this->visibleQuery()?->whereKey($id)->first();
    }

    /** @return Builder<Branch>|null */
    private function visibleQuery(?string $category = null): ?Builder
    {
        if (! Module::find('Branches')?->isEnabled() || ! class_exists(KonumFrontendService::class)
            || ! Schema::hasTable('branches') || ! Schema::hasTable('branch_categories')) {
            return null;
        }
        if (! app(KonumFrontendService::class)->ready()) {
            return null;
        }

        $query = Branch::query()
            ->with(['category', 'city', 'town', 'district', 'neighbourhood', 'street'])
            ->where('is_active', true)
            ->whereHas('category', fn (Builder $categories): Builder => $categories->where('is_active', true))
            ->orderBy('sort_order')->orderBy('id');

        if ($category !== null && $category !== '') {
            $query->whereHas('category', fn (Builder $categories): Builder => $categories->where('slug', $category));
        }

        return $query;
    }
}
