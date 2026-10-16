<?php

namespace Modules\Team\DataTables;

use Illuminate\Http\JsonResponse;
use Modules\Team\Models\TeamMember;
use Yajra\DataTables\Facades\DataTables;

class TeamMembersDataTable
{
    public function handle(): JsonResponse
    {
        return DataTables::eloquent(TeamMember::query()->select(['id', 'name', 'title', 'image', 'is_active', 'created_at']))
            ->addColumn('photo', fn (TeamMember $member): string => view('team::partials.photo', compact('member'))->render())
            ->editColumn('is_active', fn (TeamMember $member): string => view('team::partials.status', compact('member'))->render())
            ->editColumn('created_at', fn (TeamMember $member): ?string => $member->created_at?->format('d.m.Y H:i'))
            ->addColumn('actions', fn (TeamMember $member): string => view('team::partials.actions', compact('member'))->render())
            ->rawColumns(['photo', 'is_active', 'actions'])
            ->toJson();
    }
}
