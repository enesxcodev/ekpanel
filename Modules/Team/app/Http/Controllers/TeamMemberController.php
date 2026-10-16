<?php

namespace Modules\Team\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Team\DataTables\TeamMembersDataTable;
use Modules\Team\Http\Requests\SaveTeamMemberRequest;
use Modules\Team\Models\TeamMember;
use Modules\Team\Services\TeamMemberService;

class TeamMemberController extends Controller
{
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

        return view('team::create', compact('featureForm', 'featureFields'));
    }

    public function store(SaveTeamMemberRequest $request, TeamMemberService $service): RedirectResponse
    {
        $service->save($request->validated());

        return redirect()->route('admin.team.index')->with('notification', ['type' => 'success', 'message' => 'Ekip üyesi başarıyla eklendi.']);
    }

    public function edit(TeamMember $member, ModuleFeatures $features, FeatureValues $values): View
    {
        $this->authorize('team.update');
        $featureForm = $features->form('Team', 'member');
        $featureFields = $values->fields($featureForm['fields'], $member->extra_fields ?? []);

        return view('team::edit', compact('member', 'featureForm', 'featureFields'));
    }

    public function update(SaveTeamMemberRequest $request, TeamMember $member, TeamMemberService $service): RedirectResponse
    {
        $service->save($request->validated(), $member);

        return redirect()->route('admin.team.index')->with('notification', ['type' => 'success', 'message' => 'Ekip üyesi başarıyla güncellendi.']);
    }

    public function destroy(TeamMember $member, TeamMemberService $service): RedirectResponse
    {
        $this->authorize('team.delete');
        $service->delete($member);

        return redirect()->route('admin.team.index')->with('notification', ['type' => 'success', 'message' => 'Ekip üyesi başarıyla silindi.']);
    }
}
