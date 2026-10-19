<?php

namespace Modules\Team\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Team\Models\TeamMember;
use Nwidart\Modules\Facades\Module;

class TeamFrontendService
{
    /** @return Collection<int, TeamMember> */
    public function getAll(): Collection
    {
        return $this->visibleQuery()?->get() ?? new Collection;
    }

    public function getFirst(): ?TeamMember
    {
        return $this->visibleQuery()?->first();
    }

    public function find(int $id): ?TeamMember
    {
        return $this->visibleQuery()?->whereKey($id)->first();
    }

    /** @return Builder<TeamMember>|null */
    private function visibleQuery(): ?Builder
    {
        if (! Module::find('Team')?->isEnabled() || ! Schema::hasTable('team_members')) {
            return null;
        }

        $query = TeamMember::query()->with(['imageMedia'])->where('is_active', true);

        return $query;
    }
}
