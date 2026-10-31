<?php

namespace Modules\Team\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Schema;
use Modules\Team\Models\TeamMember;
use Nwidart\Modules\Facades\Module;

class TeamFrontendService
{
    public function __construct(private TeamLanguageService $languages) {}

    /** @return Collection<int, TeamMember> */
    public function getAll(?string $languageCode = null): Collection
    {
        return $this->visibleQuery($languageCode)?->get()->each(fn (TeamMember $member) => $this->localize($member)) ?? new Collection;
    }

    public function getFirst(?string $languageCode = null): ?TeamMember
    {
        $member = $this->visibleQuery($languageCode)?->first();

        return $member ? $this->localize($member) : null;
    }

    public function find(int $id, ?string $languageCode = null): ?TeamMember
    {
        $member = $this->visibleQuery($languageCode)?->whereKey($id)->first();

        return $member ? $this->localize($member) : null;
    }

    private function localize(TeamMember $member): TeamMember
    {
        $member->setRelation('selectedTranslation', $member->translations->first());

        return $member;
    }

    /** @return Builder<TeamMember>|null */
    private function visibleQuery(?string $languageCode = null): ?Builder
    {
        if (! Module::find('Team')?->isEnabled() || ! Schema::hasTable('team_members') || ! Schema::hasTable('team_member_translations') || ! Schema::hasTable('languages')) {
            return null;
        }
        $language = $this->languages->forFrontend($languageCode);
        if ($language === null) {
            return null;
        }

        $query = TeamMember::query()->with(['imageMedia', 'translations' => fn ($query) => $query->where('lang_id', $language->id)])->where('is_active', true)
            ->whereHas('translations', fn ($query) => $query->where('lang_id', $language->id));

        return $query;
    }
}
