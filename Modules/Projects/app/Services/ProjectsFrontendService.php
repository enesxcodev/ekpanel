<?php

namespace Modules\Projects\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Projects\Models\Project;
use Nwidart\Modules\Facades\Module;

class ProjectsFrontendService
{
    /** @return Collection<int, Project> */
    public function getAll(?int $categoryId = null): Collection
    {
        return $this->visibleQuery($categoryId)?->get() ?? new Collection;
    }

    public function getFirst(?int $categoryId = null): ?Project
    {
        return $this->visibleQuery($categoryId)?->first();
    }

    public function find(int $id): ?Project
    {
        return $this->visibleQuery()?->whereKey($id)->first();
    }

    /** @return Builder<Project>|null */
    private function visibleQuery(?int $categoryId = null): ?Builder
    {
        if (! Module::find('Projects')?->isEnabled() || ! Schema::hasTable('projects') || ! Schema::hasTable('project_categories') || ! Schema::hasTable('project_images')) {
            return null;
        }

        $query = Project::query()->with(['category', 'coverMedia', 'images'])->published();

        if ($categoryId !== null) {
            $query->where('project_category_id', $categoryId);
        }

        return $query;
    }
}
