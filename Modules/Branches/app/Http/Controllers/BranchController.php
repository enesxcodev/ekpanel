<?php

namespace Modules\Branches\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Settings\Language;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Modules\Branches\DataTables\BranchesDataTable;
use Modules\Branches\Http\Requests\SaveBranchRequest;
use Modules\Branches\Models\Branch;
use Modules\Branches\Models\BranchCategory;
use Modules\Branches\Services\BranchesLanguageService;
use Modules\Branches\Services\BranchLocationService;
use Modules\Branches\Services\BranchService;

class BranchController extends Controller
{
    public function index(BranchesLanguageService $languages, Request $request): View
    {
        $this->authorize('branches.view');

        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);
        $categories = BranchCategory::query()->leftJoin('branch_category_translations as localized', function ($join) use ($selectedLanguage): void {
            $join->on('localized.branch_category_id', '=', 'branch_categories.id')->where('localized.lang_id', '=', $selectedLanguage?->id ?? 0);
        })->orderBy('branch_categories.sort_order')->orderBy('branch_categories.id')->get(['branch_categories.id', 'localized.name']);

        return view('branches::index', compact('categories', 'activeLanguages', 'selectedLanguage'));
    }

    public function data(BranchesDataTable $dataTable): JsonResponse
    {
        $this->authorize('branches.view');

        return $dataTable->handle();
    }

    public function create(BranchLocationService $locations, BranchesLanguageService $languages, Request $request): View
    {
        $this->authorize('branches.create');

        return $this->form($locations, languages: $languages, request: $request);
    }

    public function store(SaveBranchRequest $request, BranchService $service, NotifyService $notifications): RedirectResponse
    {
        $branch = $service->save($request->validated());
        $language = Language::query()->findOrFail($request->validated('lang_id'));
        request()->attributes->set('branches_language', $language->code);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Şube / bayi eklendi',
            'Bir şube veya bayi kaydı eklendi.',
            route('admin.branches.edit', ['branch' => $branch->id, 'lang' => $language->code], absolute: false),
        );

        return redirect()->route('admin.branches.edit', ['branch' => $branch->id, 'lang' => $language->code])->with('notification', [
            'type' => 'success', 'message' => 'Şube / bayi başarıyla eklendi.',
        ]);
    }

    public function edit(Branch $branch, BranchLocationService $locations, BranchesLanguageService $languages, Request $request): View
    {
        $this->authorize('branches.update');

        return $this->form($locations, $branch, $languages, $request);
    }

    public function update(SaveBranchRequest $request, Branch $branch, BranchService $service, NotifyService $notifications): RedirectResponse
    {
        $branch = $service->save($request->validated(), $branch);
        $language = Language::query()->findOrFail($request->validated('lang_id'));
        request()->attributes->set('branches_language', $language->code);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Şube / bayi güncellendi',
            'Bir şube veya bayi kaydı güncellendi.',
            route('admin.branches.edit', ['branch' => $branch->id, 'lang' => $language->code], absolute: false),
        );

        return redirect()->route('admin.branches.edit', ['branch' => $branch->id, 'lang' => $language->code])->with('notification', [
            'type' => 'success', 'message' => 'Şube / bayi başarıyla güncellendi.',
        ]);
    }

    public function destroy(Branch $branch, BranchService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('branches.delete');
        $service->delete($branch);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Şube / bayi silindi',
            'Bir şube veya bayi kaydı silindi.',
            route('admin.branches.index', absolute: false),
        );

        return redirect()->route('admin.branches.index')->with('notification', [
            'type' => 'success', 'message' => 'Şube / bayi başarıyla silindi.',
        ]);
    }

    public function locationOptions(Request $request, BranchLocationService $locations): JsonResponse
    {
        $this->authorize('branches.view');
        $data = $request->validate([
            'entity' => ['required', 'string', Rule::in(array_keys($locations->fields()))],
            'city_id' => ['nullable', 'integer', 'min:1'],
            'town_id' => ['nullable', 'integer', 'min:1'],
            'district_id' => ['nullable', 'integer', 'min:1'],
            'neighbourhood_id' => ['nullable', 'integer', 'min:1'],
            'search' => ['nullable', 'string', 'max:100'],
        ]);
        $parents = $locations->parentsFor($data['entity'], $data);
        $items = $locations->options($data['entity'], $parents, null, $data['search'] ?? '')
            ->map(fn ($record): array => ['id' => $record->id, 'name' => $record->name]);

        return response()->json(['items' => $items]);
    }

    private function form(BranchLocationService $locations, ?Branch $branch = null, ?BranchesLanguageService $languages = null, ?Request $request = null): View
    {
        $languages ??= app(BranchesLanguageService::class);
        $request ??= request();
        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);
        $translation = $branch !== null && $selectedLanguage !== null ? $branch->translations()->where('lang_id', $selectedLanguage->id)->first() : null;
        $translatedLanguageIds = $branch ? $languages->translatedLanguageIds($branch) : [];
        $categories = BranchCategory::query()->where(function (Builder $query) use ($branch): void {
            $query->where('branch_categories.is_active', true);
            if ($branch !== null) {
                $query->orWhere('branch_categories.id', $branch->branch_category_id);
            }
        })->leftJoin('branch_category_translations as localized', function ($join) use ($selectedLanguage): void {
            $join->on('localized.branch_category_id', '=', 'branch_categories.id')->where('localized.lang_id', '=', $selectedLanguage?->id ?? 0);
        })->orderBy('branch_categories.sort_order')->orderBy('branch_categories.id')
            ->get(['branch_categories.id', 'localized.name'])->map(fn ($category): array => ['value' => $category->id, 'label' => $category->name ?? 'Çeviri eksik'])->all();
        $selection = [];
        foreach (array_values($locations->fields()) as $field) {
            $selection[$field] = old($field, $branch?->{$field});
        }
        foreach (['name', 'address_detail', 'phone', 'email', 'website', 'working_hours', 'map_url'] as $field) {
            $selection[$field] = old($field, $translation?->{$field});
        }
        $locationOptions = [];
        foreach ($locations->fields() as $entity => $field) {
            $locationOptions[$field] = $locations->options($entity, $locations->parentsFor($entity, $selection), isset($selection[$field]) ? (int) $selection[$field] : null)
                ->map(fn ($record): array => ['id' => $record->id, 'name' => $record->name])->all();
        }
        $locationOptionsUrl = route('admin.branches.location-options');
        $locationReady = $locations->ready();

        return view($branch === null ? 'branches::create' : 'branches::edit', compact('branch', 'categories', 'locationOptions', 'locationOptionsUrl', 'locationReady', 'activeLanguages', 'selectedLanguage', 'translation', 'translatedLanguageIds', 'selection'));
    }
}
