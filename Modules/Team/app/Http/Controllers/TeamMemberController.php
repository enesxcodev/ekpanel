<?php

namespace Modules\Team\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Team\DataTables\TeamMembersDataTable;
use Modules\Team\Http\Requests\SaveTeamMemberRequest;
use Modules\Team\Models\TeamMember;
use Modules\Team\Services\TeamMemberService;

class TeamMemberController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(): View
    {
        $this->authorize('team.view');

        return view('team::index');
    }

    public function data(TeamMembersDataTable $dataTable): JsonResponse
    {
        $this->authorize('team.view');

        return $dataTable->handle();
    }

    public function create(ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('team.create');
        $featureForm = $features->form('Team', 'member');
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('team::create', compact('featureForm', 'featureFields') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function store(SaveTeamMemberRequest $request, TeamMemberService $service, NotifyService $notifications): RedirectResponse
    {
        $member = $service->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Ekip üyesi eklendi',
            'Bir ekip üyesi eklendi.',
            route('admin.team.edit', ['member' => $member->id], absolute: false),
        );

        return redirect()->route('admin.team.index')->with('notification', ['type' => 'success', 'message' => 'Ekip üyesi başarıyla eklendi.']);
    }

    public function edit(TeamMember $member, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('team.update');
        $featureForm = $features->form('Team', 'member');
        $member->load('imageMedia');
        $featureFields = $values->fields($featureForm['fields'], $member->extra_fields ?? []);

        return view('team::edit', compact('member', 'featureForm', 'featureFields') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
    }

    public function update(SaveTeamMemberRequest $request, TeamMember $member, TeamMemberService $service, NotifyService $notifications): RedirectResponse
    {
        $member = $service->save($request->validated(), $member, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Ekip üyesi güncellendi',
            'Bir ekip üyesi güncellendi.',
            route('admin.team.edit', ['member' => $member->id], absolute: false),
        );

        return redirect()->route('admin.team.index')->with('notification', ['type' => 'success', 'message' => 'Ekip üyesi başarıyla güncellendi.']);
    }

    public function destroy(TeamMember $member, TeamMemberService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('team.delete');
        $service->delete($member);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Ekip üyesi silindi',
            'Bir ekip üyesi silindi.',
            route('admin.team.index', absolute: false),
        );

        return redirect()->route('admin.team.index')->with('notification', ['type' => 'success', 'message' => 'Ekip üyesi başarıyla silindi.']);
    }
}
