<?php

namespace Modules\Projects\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Projects\Models\Project;
use Nwidart\Modules\Facades\Module;

class ProjectsFrontendService
{
    public function __construct(private ProjectsLanguageService $languages) {}

    /** @return Collection<int, Project> */
    public function getAll(?int $categoryId = null, ?string $languageCode = null): Collection
    {
        return $this->visibleQuery($categoryId, $languageCode)?->get() ?? new Collection;
    }

    public function getFirst(?int $categoryId = null, ?string $languageCode = null): ?Project
    {
        return $this->visibleQuery($categoryId, $languageCode)?->first();
    }

    public function find(int $id, ?string $languageCode = null): ?Project
    {
        return $this->visibleQuery(null, $languageCode)?->whereKey($id)->first();
    }

    /** @return Builder<Project>|null */
    private function visibleQuery(?int $categoryId = null, ?string $languageCode = null): ?Builder
    {
        if (! Module::find('Projects')?->isEnabled() || ! Schema::hasTable('projects') || ! Schema::hasTable('project_categories') || ! Schema::hasTable('project_images') || ! Schema::hasTable('project_translations') || ! Schema::hasTable('project_category_translations') || ! Schema::hasTable('languages')) {
            return null;
        }
        $language = $this->languages->forFrontend($languageCode);
        if ($language === null) {
            return null;
        }

        $query = Project::query()->with(['selectedTranslation', 'category.selectedTranslation', 'coverMedia', 'images'])
            ->whereHas('translations', fn (Builder $translation): Builder => $translation->where('lang_id', $language->id))
            ->whereHas('category.translations', fn (Builder $translation): Builder => $translation->where('lang_id', $language->id))
            ->published();

        if ($categoryId !== null) {
            $query->where('project_category_id', $categoryId);
        }

        return $query;
    }
}
