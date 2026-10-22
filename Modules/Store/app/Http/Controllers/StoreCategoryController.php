<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Store\Http\Requests\SaveStoreCategoryRequest;
use Modules\Store\Models\StoreCategory;
use Yajra\DataTables\Facades\DataTables;

class StoreCategoryController extends Controller
{
    public function index(): View
    {
        return view('store::categories.index');
    }

    public function data(): JsonResponse
    {
        return DataTables::eloquent(StoreCategory::query())->addColumn('actions', fn ($category): string => view('store::categories.actions', compact('category'))->render())->rawColumns(['actions'])->toJson();
    }

    public function create(): View
    {
        return view('store::categories.form', ['category' => null]);
    }

    public function edit(StoreCategory $category): View
    {
        return view('store::categories.form', compact('category'));
    }

    public function store(SaveStoreCategoryRequest $request, NotifyService $notifications): RedirectResponse
    {
        $category = StoreCategory::query()->create($request->validated());
        $notifications->sendToAdminsForActor($request->user('admin'), 'Mağaza kategorisi eklendi', 'Kategori eklendi.', route('admin.store.categories.edit', $category, absolute: false));

        return redirect()->route('admin.store.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori eklendi.']);
    }

    public function update(SaveStoreCategoryRequest $request, StoreCategory $category, NotifyService $notifications): RedirectResponse
    {
        $category->update($request->validated());
        $notifications->sendToAdminsForActor($request->user('admin'), 'Mağaza kategorisi güncellendi', 'Kategori güncellendi.', route('admin.store.categories.edit', $category, absolute: false));

        return redirect()->route('admin.store.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori güncellendi.']);
    }

    public function destroy(Request $request, StoreCategory $category, NotifyService $notifications): RedirectResponse
    {
        $category->delete();
        $notifications->sendToAdminsForActor($request->user('admin'), 'Mağaza kategorisi silindi', 'Kategori silindi.', route('admin.store.categories.index', absolute: false));

        return redirect()->route('admin.store.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori silindi.']);
    }
}
