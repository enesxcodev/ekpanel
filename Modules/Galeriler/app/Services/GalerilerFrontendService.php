<?php

namespace Modules\Galeriler\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Galeriler\Models\GalerilerCategory;
use Modules\Galeriler\Models\GalerilerItem;
use Nwidart\Modules\Facades\Module;

class GalerilerFrontendService
{
    /** @return Collection<int, GalerilerItem> */
    public function getAll(?int $categoryId = null): Collection
    {
        return $this->visibleQuery($categoryId)?->get() ?? new Collection;
    }

    public function getFirst(?int $categoryId = null): ?GalerilerItem
    {
        return $this->visibleQuery($categoryId)?->first();
    }

    public function find(int $id): ?GalerilerItem
    {
        return $this->visibleQuery()?->whereKey($id)->first();
    }

    /** @return Builder<GalerilerItem>|null */
    private function visibleQuery(?int $categoryId = null): ?Builder
    {
        if (! Module::find('Galeriler')?->isEnabled() || ! Schema::hasTable('galeriler_items') || ! Schema::hasTable('galeriler_categories')) {
            return null;
        }

        $query = GalerilerItem::query()->with(['category', 'imageMedia'])->orderBy('sort_order')->orderBy('id');

        if ($categoryId !== null) {
            $category = GalerilerCategory::query()->find($categoryId);
            $query->whereIn('galeriler_category_id', $category === null ? [] : [$category->id, ...$category->descendantIds()]);
        }

        return $query;
    }
}
