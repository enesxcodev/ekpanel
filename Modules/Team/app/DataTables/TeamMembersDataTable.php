<?php

namespace Modules\Team\DataTables;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Team\Models\TeamMember;
use Modules\Team\Services\TeamLanguageService;
use Yajra\DataTables\Facades\DataTables;

class TeamMembersDataTable
{
    public function handle(Request $request, TeamLanguageService $languages): JsonResponse
    {
        $language = $languages->forAdmin($request->query('lang'));
        $query = TeamMember::query()->leftJoin('team_member_translations as localized', function ($join) use ($language): void {
            $join->on('localized.team_member_id', '=', 'team_members.id')->where('localized.lang_id', '=', $language?->id ?? 0);
        })->select(['team_members.id', 'localized.name as name', 'localized.title as title', 'team_members.image', 'team_members.image_media_id', 'team_members.is_active', 'team_members.created_at']);

        $languageCode = $language?->code ?? 'tr';

        return DataTables::eloquent($query)
            ->filterColumn('name', fn ($query, $keyword) => $query->where('localized.name', 'like', '%'.$keyword.'%'))
            ->orderColumn('name', 'localized.name $1')
            ->filterColumn('title', fn ($query, $keyword) => $query->where('localized.title', 'like', '%'.$keyword.'%'))
            ->orderColumn('title', 'localized.title $1')
            ->addColumn('photo', fn (TeamMember $member): string => view('team::partials.photo', compact('member'))->render())
            ->editColumn('is_active', fn (TeamMember $member): string => view('team::partials.status', compact('member'))->render())
            ->editColumn('created_at', fn (TeamMember $member): ?string => $member->created_at?->format('d.m.Y H:i'))
            ->addColumn('actions', function (TeamMember $member) use ($languageCode): string {
                return view('team::partials.actions', compact('member', 'languageCode'))->render();
            })
            ->rawColumns(['photo', 'is_active', 'actions'])
            ->toJson();
    }
}
