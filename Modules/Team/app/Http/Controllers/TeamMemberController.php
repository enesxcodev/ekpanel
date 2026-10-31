<?php

namespace Modules\Team\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Settings\Language;
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
use Modules\Team\Services\TeamLanguageService;
use Modules\Team\Services\TeamMemberService;

class TeamMemberController extends Controller
{
    public function __construct(private MediaService $media) {}

    public function index(TeamLanguageService $languages): View
    {
        $this->authorize('team.view');

        return view('team::index', ['activeLanguages' => $languages->active(), 'selectedLanguage' => $languages->forAdmin(request()->query('lang'))]);
    }

    public function data(TeamMembersDataTable $dataTable, Request $request): JsonResponse
    {
        $this->authorize('team.view');

        return $dataTable->handle($request, app(TeamLanguageService::class));
    }

    public function create(ModuleFeatures $features, FeatureValues $values, TeamLanguageService $languages): View
    {
        $this->authorize('team.create');
        $featureForm = $features->form('Team', 'member');
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->forAdmin(request()->query('lang', 'tr'));
        if ($selectedLanguage?->code !== 'tr') {
            abort(404);
        }
        $featureFields = $values->fields($featureForm['fields'], []);

        return view('team::create', compact('featureForm', 'featureFields', 'activeLanguages', 'selectedLanguage') + ['mediaConfig' => $this->media->configuration(auth('admin')->user()), 'translation' => null]);
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

        return redirect()->route('admin.team.edit', ['member' => $member->id, 'lang' => 'tr'])->with('notification', ['type' => 'success', 'message' => 'Ekip üyesi başarıyla eklendi.']);
    }

    public function edit(TeamMember $member, ModuleFeatures $features, FeatureValues $values, TeamLanguageService $languages): View
    {
        $this->authorize('team.update');
        $featureForm = $features->form('Team', 'member');
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->forAdmin(request()->query('lang'));
        $member->load(['imageMedia', 'translations' => fn ($query) => $query->where('lang_id', $selectedLanguage?->id), 'translations.language']);
        $translation = $member->translations->first();
        $featureFields = $values->fields($featureForm['fields'], $translation?->extra_fields ?? []);

        return view('team::edit', compact('member', 'featureForm', 'featureFields', 'activeLanguages', 'selectedLanguage', 'translation') + ['mediaConfig' => $this->media->configuration(auth('admin')->user())]);
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

        $code = Language::query()->findOrFail($request->validated('lang_id'))->code;

        return redirect()->route('admin.team.edit', ['member' => $member->id, 'lang' => $code])->with('notification', ['type' => 'success', 'message' => 'Ekip üyesi başarıyla güncellendi.']);
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
