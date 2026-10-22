<?php

namespace Modules\Branches\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Modules\Branches\DataTables\BranchCategoriesDataTable;
use Modules\Branches\Http\Requests\SaveBranchCategoryRequest;
use Modules\Branches\Models\BranchCategory;

class BranchCategoryController extends Controller
{
    public function index(): View
    {
        $this->authorize('branches.categories.view');

        return view('branches::categories.index');
    }

    public function data(BranchCategoriesDataTable $dataTable): JsonResponse
    {
        $this->authorize('branches.categories.view');

        return $dataTable->handle();
    }

    public function create(): View
    {
        $this->authorize('branches.categories.create');

        return view('branches::categories.create');
    }

    public function store(SaveBranchCategoryRequest $request, NotifyService $notifications): RedirectResponse
    {
        $category = BranchCategory::query()->create($request->validated());
        $notifications->sendToAdminsForActor(
            $request->user('admin'), 'Şube / bayi kategorisi eklendi', 'Bir şube / bayi kategorisi eklendi.',
            route('admin.branches.categories.edit', $category, absolute: false),
        );

        return redirect()->route('admin.branches.categories.index')->with('notification', [
            'type' => 'success', 'message' => 'Kategori başarıyla eklendi.',
        ]);
    }

    public function edit(BranchCategory $category): View
    {
        $this->authorize('branches.categories.update');

        return view('branches::categories.edit', compact('category'));
    }

    public function update(SaveBranchCategoryRequest $request, BranchCategory $category, NotifyService $notifications): RedirectResponse
    {
        $category->update($request->validated());
        $notifications->sendToAdminsForActor(
            $request->user('admin'), 'Şube / bayi kategorisi güncellendi', 'Bir şube / bayi kategorisi güncellendi.',
            route('admin.branches.categories.edit', $category, absolute: false),
        );

        return redirect()->route('admin.branches.categories.index')->with('notification', [
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
