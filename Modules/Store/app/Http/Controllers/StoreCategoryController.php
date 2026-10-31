<?php

namespace Modules\Store\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Settings\Language;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Store\Http\Requests\SaveStoreCategoryRequest;
use Modules\Store\Models\StoreCategory;
use Modules\Store\Models\StoreCategoryTranslation;
use Modules\Store\Services\StoreLanguageService;
use Yajra\DataTables\Facades\DataTables;

class StoreCategoryController extends Controller
{
    public function index(Request $request, StoreLanguageService $languages): View
    {
        $language = $languages->forAdmin($request->query('lang'));

        return view('store::categories.index', ['dataUrl' => route('admin.store.categories.data', ['lang' => $language?->code ?? 'tr']), 'languageCode' => $language?->code ?? 'tr']);
    }

    public function data(): JsonResponse
    {
        $language = app(StoreLanguageService::class)->forAdmin(request()->query('lang'));

        return DataTables::eloquent(StoreCategory::query()->with('translation'))
            ->addColumn('name', fn (StoreCategory $category): string => $category->translation?->name ?? __('store::store.translation_missing'))
            ->addColumn('slug', fn (StoreCategory $category): string => $category->translation?->slug ?? '')
            ->filterColumn('name', fn ($query, string $keyword) => $query->whereHas('translations', fn ($translation) => $translation->where('lang_id', $language?->id)->where('name', 'like', '%'.$keyword.'%')))
            ->filterColumn('slug', fn ($query, string $keyword) => $query->whereHas('translations', fn ($translation) => $translation->where('lang_id', $language?->id)->where('slug', 'like', '%'.$keyword.'%')))
            ->orderColumn('name', fn ($query, string $order) => $query->orderBy(StoreCategoryTranslation::query()->select('name')->whereColumn('store_category_id', 'store_categories.id')->where('lang_id', $language?->id), $order))
            ->orderColumn('slug', fn ($query, string $order) => $query->orderBy(StoreCategoryTranslation::query()->select('slug')->whereColumn('store_category_id', 'store_categories.id')->where('lang_id', $language?->id), $order))
            ->addColumn('actions', fn (StoreCategory $category): string => view('store::categories.actions', ['category' => $category, 'languageCode' => $language?->code ?? 'tr'])->render())->rawColumns(['actions'])->toJson();
    }

    public function create(Request $request, StoreLanguageService $languages): View
    {
        $language = $languages->forAdmin($request->query('lang'));

        return view('store::categories.form', ['category' => null, 'language' => $language, 'languages' => $languages->active()]);
    }

    public function edit(Request $request, StoreCategory $category, StoreLanguageService $languages): View
    {
        $language = $languages->forAdmin($request->query('lang'));
        $category->load('translations');
        if ($translation = $category->translations->firstWhere('lang_id', $language?->id)) {
            $category->setRelation('translation', $translation);
        }

        return view('store::categories.form', ['category' => $category, 'language' => $language, 'languages' => $languages->active()]);
    }

    public function store(SaveStoreCategoryRequest $request, NotifyService $notifications): RedirectResponse
    {
        $data = $request->validated();
        abort_unless(Language::query()->find($data['lang_id'])?->code === StoreLanguageService::PrimaryCode, 422, 'Yeni mağaza kategorileri önce Türkçe oluşturulmalıdır.');
        $category = StoreCategory::query()->create(['sort_order' => $data['sort_order'], 'is_active' => $data['is_active']]);
        $category->translations()->create(['lang_id' => $data['lang_id'], 'name' => $data['name'], 'slug' => $data['slug']]);
        $notifications->sendToAdminsForActor($request->user('admin'), 'Mağaza kategorisi eklendi', 'Kategori eklendi.', route('admin.store.categories.edit', $category, absolute: false));

        return redirect()->route('admin.store.categories.edit', ['category' => $category, 'lang' => Language::query()->find($data['lang_id'])?->code ?? 'tr'])->with('notification', ['type' => 'success', 'message' => 'Kategori eklendi.']);
    }

    public function update(SaveStoreCategoryRequest $request, StoreCategory $category, NotifyService $notifications): RedirectResponse
    {
        $data = $request->validated();
        if (Language::query()->find($data['lang_id'])?->code === StoreLanguageService::PrimaryCode) {
            $category->update(['sort_order' => $data['sort_order'], 'is_active' => $data['is_active']]);
        }
        $category->translations()->updateOrCreate(['lang_id' => $data['lang_id']], ['name' => $data['name'], 'slug' => $data['slug']]);
        $notifications->sendToAdminsForActor($request->user('admin'), 'Mağaza kategorisi güncellendi', 'Kategori güncellendi.', route('admin.store.categories.edit', $category, absolute: false));

        return redirect()->route('admin.store.categories.edit', ['category' => $category, 'lang' => Language::query()->find($data['lang_id'])?->code ?? 'tr'])->with('notification', ['type' => 'success', 'message' => 'Kategori güncellendi.']);
    }

    public function destroy(Request $request, StoreCategory $category, NotifyService $notifications): RedirectResponse
    {
        $category->delete();
        $notifications->sendToAdminsForActor($request->user('admin'), 'Mağaza kategorisi silindi', 'Kategori silindi.', route('admin.store.categories.index', absolute: false));

        return redirect()->route('admin.store.categories.index')->with('notification', ['type' => 'success', 'message' => 'Kategori silindi.']);
    }
}
