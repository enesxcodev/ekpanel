<?php

namespace Modules\Branches\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Settings\Language;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Branches\DataTables\BranchCategoriesDataTable;
use Modules\Branches\Http\Requests\SaveBranchCategoryRequest;
use Modules\Branches\Models\BranchCategory;
use Modules\Branches\Services\BranchesLanguageService;
use Modules\Branches\Services\BranchService;

class BranchCategoryController extends Controller
{
    public function index(BranchesLanguageService $languages, Request $request): View
    {
        $this->authorize('branches.categories.view');

        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);

        return view('branches::categories.index', compact('activeLanguages', 'selectedLanguage'));
    }

    public function data(BranchCategoriesDataTable $dataTable): JsonResponse
    {
        $this->authorize('branches.categories.view');

        return $dataTable->handle();
    }

    public function create(BranchesLanguageService $languages, Request $request): View
    {
        $this->authorize('branches.categories.create');

        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);
        $translatedLanguageIds = [];
        $translation = null;

        return view('branches::categories.create', compact('activeLanguages', 'selectedLanguage', 'translatedLanguageIds', 'translation'));
    }

    public function store(SaveBranchCategoryRequest $request, BranchService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->saveCategory($request->validated());
        $language = app(BranchesLanguageService::class)->forAdmin(Language::query()->findOrFail($request->validated('lang_id'))->code);
        $notifications->sendToAdminsForActor(
            $request->user('admin'), 'Şube / bayi kategorisi eklendi', 'Bir şube / bayi kategorisi eklendi.',
            route('admin.branches.categories.edit', ['category' => $category->id, 'lang' => $language?->code], absolute: false),
        );

        return redirect()->route('admin.branches.categories.edit', ['category' => $category->id, 'lang' => $language?->code])->with('notification', [
            'type' => 'success', 'message' => 'Kategori başarıyla eklendi.',
        ]);
    }

    public function edit(BranchCategory $category, BranchesLanguageService $languages, Request $request): View
    {
        $this->authorize('branches.categories.update');

        $activeLanguages = $languages->active();
        $selectedLanguage = $languages->selectedAdmin($request);
        $translation = $selectedLanguage ? $category->translations()->where('lang_id', $selectedLanguage->id)->first() : null;
        $translatedLanguageIds = $languages->translatedLanguageIds($category);

        return view('branches::categories.edit', compact('category', 'activeLanguages', 'selectedLanguage', 'translation', 'translatedLanguageIds'));
    }

    public function update(SaveBranchCategoryRequest $request, BranchCategory $category, BranchService $service, NotifyService $notifications): RedirectResponse
    {
        $category = $service->saveCategory($request->validated(), $category);
        $language = app(BranchesLanguageService::class)->forAdmin(Language::query()->findOrFail($request->validated('lang_id'))->code);
        $notifications->sendToAdminsForActor(
            $request->user('admin'), 'Şube / bayi kategorisi güncellendi', 'Bir şube / bayi kategorisi güncellendi.',
            route('admin.branches.categories.edit', ['category' => $category->id, 'lang' => $language?->code], absolute: false),
        );

        return redirect()->route('admin.branches.categories.edit', ['category' => $category->id, 'lang' => $language?->code])->with('notification', [
            'type' => 'success', 'message' => 'Kategori başarıyla güncellendi.',
        ]);
    }

    public function destroy(Request $request, BranchCategory $category, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('branches.categories.delete');
        if ($category->branches()->exists()) {
            throw ValidationException::withMessages([
                'category' => 'Bu kategoriye bağlı kayıtlar var. Silmeden önce kayıtları başka kategoriye taşıyın.',
            ]);
        }
        $category->delete();
        $notifications->sendToAdminsForActor(
            $request->user('admin'), 'Şube / bayi kategorisi silindi', 'Bir şube / bayi kategorisi silindi.',
            route('admin.branches.categories.index', absolute: false),
        );

        return redirect()->route('admin.branches.categories.index')->with('notification', [
            'type' => 'success', 'message' => 'Kategori başarıyla silindi.',
        ]);
    }
}
