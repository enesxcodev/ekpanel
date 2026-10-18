<?php

namespace Modules\Galeriler\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Modules\Galeriler\Http\Requests\BulkGalerilerItemsRequest;
use Modules\Galeriler\Http\Requests\GalerilerIndexRequest;
use Modules\Galeriler\Http\Requests\ReorderGalerilerItemsRequest;
use Modules\Galeriler\Http\Requests\SaveGalerilerItemRequest;
use Modules\Galeriler\Models\GalerilerCategory;
use Modules\Galeriler\Models\GalerilerItem;
use Modules\Galeriler\Services\GalerilerItemService;

class GalerilerItemController extends Controller
{
    public function index(GalerilerIndexRequest $request): View
    {
        $filters = $request->validated();
        $query = GalerilerItem::query()->with(['category', 'imageMedia']);
        if (! empty($filters['category_id'])) {
            $query->where('galeriler_category_id', $filters['category_id']);
        }
        if (! empty($filters['type'])) {
            $query->where('type', $filters['type']);
        }
        if ($request->filled('search')) {
            $search = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $filters['search']).'%';
            $query->where(fn (Builder $query): Builder => $query->whereRaw("title LIKE ? ESCAPE '!'", [$search])->orWhereRaw("description LIKE ? ESCAPE '!'", [$search]));
        }

        return view('galeriler::index', [
            'items' => $query->orderBy('sort_order')->orderBy('id')->paginate(24)->withQueryString(),
            'categories' => GalerilerCategory::query()->orderBy('name')->get(),
            'filters' => $filters,
            'canReorder' => ! empty($filters['category_id']) && empty($filters['type']) && ! $request->filled('search') && $request->user('admin')->can('galeriler.update'),
        ]);
    }

    public function create(ModuleFeatures $features, FeatureValues $values, MediaService $media): View
    {
        $this->authorize('galeriler.create');

        return $this->form(null, $features, $values, $media);
    }

    public function store(SaveGalerilerItemRequest $request, GalerilerItemService $service): RedirectResponse
    {
        $service->save($request->validated(), null, $request->user('admin'));

        return redirect()->route('admin.galeriler.index')->with('notification', ['type' => 'success', 'message' => 'Galeri kayıtları eklendi.']);
    }

    public function edit(GalerilerItem $item, ModuleFeatures $features, FeatureValues $values, MediaService $media): View
    {
        $this->authorize('galeriler.update');

        return $this->form($item, $features, $values, $media);
    }

    public function update(SaveGalerilerItemRequest $request, GalerilerItem $item, GalerilerItemService $service): RedirectResponse
    {
        $service->save($request->validated(), $item, $request->user('admin'));

        return redirect()->route('admin.galeriler.index')->with('notification', ['type' => 'success', 'message' => 'Galeri kaydı güncellendi.']);
    }

    public function destroy(GalerilerItem $item, GalerilerItemService $service): RedirectResponse
    {
        $this->authorize('galeriler.delete');
        $service->delete($item);

        return redirect()->route('admin.galeriler.index')->with('notification', ['type' => 'success', 'message' => 'Galeri kaydı silindi.']);
    }

    public function bulk(BulkGalerilerItemsRequest $request, GalerilerItemService $service): RedirectResponse
    {
        $service->bulk($request->validated());

        return redirect()->route('admin.galeriler.index')->with('notification', ['type' => 'success', 'message' => 'Toplu işlem tamamlandı.']);
    }

    public function reorder(ReorderGalerilerItemsRequest $request, GalerilerItemService $service): JsonResponse
    {
        $service->reorder($request->integer('category_id'), $request->validated('ids'));

        return response()->json(['message' => 'Sıralama kaydedildi.']);
    }

    private function form(?GalerilerItem $item, ModuleFeatures $features, FeatureValues $values, MediaService $media): View
    {
        $featureForm = $features->form('Galeriler', 'item');
        $featureFields = $values->fields($featureForm['fields'], $item?->extra_fields ?? []);
        $categoryOptions = GalerilerCategory::query()->orderBy('name')->get()->map(fn (GalerilerCategory $entry): array => ['value' => $entry->id, 'label' => $entry->name])->all();
        $mediaConfig = $media->configuration(auth('admin')->user());

        return view($item ? 'galeriler::edit' : 'galeriler::create', compact('item', 'featureForm', 'featureFields', 'categoryOptions', 'mediaConfig'));
    }
}
