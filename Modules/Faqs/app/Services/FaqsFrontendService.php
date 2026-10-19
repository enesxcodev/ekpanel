<?php

namespace Modules\Faqs\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Faqs\Models\Faq;
use Nwidart\Modules\Facades\Module;

class FaqsFrontendService
{
    /** @return Collection<int, Faq> */
    public function getAll(?int $categoryId = null): Collection
    {
        return $this->visibleQuery($categoryId)?->get() ?? new Collection;
    }

    public function getFirst(?int $categoryId = null): ?Faq
    {
        return $this->visibleQuery($categoryId)?->first();
    }

    public function find(int $id): ?Faq
    {
        return $this->visibleQuery()?->whereKey($id)->first();
    }

    /** @return Builder<Faq>|null */
    private function visibleQuery(?int $categoryId = null): ?Builder
    {
        if (! Module::find('Faqs')?->isEnabled() || ! Schema::hasTable('faqs') || ! Schema::hasTable('faq_categories')) {
            return null;
        }

        $query = Faq::query()->with(['category']);

        if ($categoryId !== null) {
            $query->where('category_id', $categoryId);
        }

        return $query;
    }
}
