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
        $branches = $this->visibleQuery($category)?->limit(max(1, min(100, $limit)))->get() ?? new Collection;
        $branches->each(fn (Branch $branch) => $this->applyTranslations($branch));

        return $branches;
    }

    public function getFirst(?string $category = null): ?Branch
    {
        $branch = $this->visibleQuery($category)?->first();
        if ($branch !== null) {
            $this->applyTranslations($branch);
        }

        return $branch;
    }

    public function find(int $id): ?Branch
    {
        $branch = $this->visibleQuery()?->whereKey($id)->first();
        if ($branch !== null) {
            $this->applyTranslations($branch);
        }

        return $branch;
    }

    private function applyTranslations(Branch $branch): void
    {
        $translation = $branch->translations->first();
        if ($translation !== null) {
            foreach (['name', 'address_detail', 'phone', 'email', 'website', 'working_hours', 'map_url'] as $field) {
                $branch->setAttribute($field, $translation->{$field});
            }
        }
        if ($branch->category !== null) {
            $branch->category->setAttribute('name', $branch->category->translations->first()?->name);
        }
    }

    /** @return Builder<Branch>|null */
    private function visibleQuery(?string $category = null): ?Builder
    {
        if (! Module::find('Branches')?->isEnabled() || ! class_exists(KonumFrontendService::class)
            || ! Schema::hasTable('branches') || ! Schema::hasTable('branch_categories') || ! Schema::hasTable('languages')
            || ! Schema::hasTable('branch_translations') || ! Schema::hasTable('branch_category_translations')) {
            return null;
        }
        if (! app(KonumFrontendService::class)->ready()) {
            return null;
        }

        $language = app(BranchesLanguageService::class)->forFrontend();
        if ($language === null) {
            return null;
        }
        $query = Branch::query()
            ->with(['translations' => fn ($translations) => $translations->where('lang_id', $language->id), 'category.translations' => fn ($translations) => $translations->where('lang_id', $language->id), 'city', 'town', 'district', 'neighbourhood', 'street'])
            ->where('is_active', true)
            ->whereHas('category', fn (Builder $categories): Builder => $categories->where('is_active', true)->whereHas('translations', fn (Builder $translations): Builder => $translations->where('lang_id', $language->id)))
            ->orderBy('sort_order')->orderBy('id');

        if ($category !== null && $category !== '') {
            $query->whereHas('category', fn (Builder $categories): Builder => $categories->where('slug', $category)->whereHas('translations', fn (Builder $translations): Builder => $translations->where('lang_id', $language->id)));
        }

        $query->whereHas('translations', fn (Builder $translations): Builder => $translations->where('lang_id', $language->id));

        return $query;
    }
}
