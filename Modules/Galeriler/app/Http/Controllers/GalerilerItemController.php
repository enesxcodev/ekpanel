<?php

namespace Modules\Galeriler\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use App\Services\NotifyService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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

    public function store(SaveGalerilerItemRequest $request, GalerilerItemService $service, NotifyService $notifications): RedirectResponse
    {
        $item = $service->save($request->validated(), null, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Galeri kaydı eklendi',
            'Bir galeri kaydı eklendi.',
            route('admin.galeriler.edit', ['item' => $item->id], absolute: false),
        );

        return redirect()->route('admin.galeriler.index')->with('notification', ['type' => 'success', 'message' => 'Galeri kayıtları eklendi.']);
    }

    public function edit(GalerilerItem $item, ModuleFeatures $features, FeatureValues $values, MediaService $media): View
    {
        $this->authorize('galeriler.update');

        return $this->form($item, $features, $values, $media);
    }

    public function update(SaveGalerilerItemRequest $request, GalerilerItem $item, GalerilerItemService $service, NotifyService $notifications): RedirectResponse
    {
        $service->save($request->validated(), $item, $request->user('admin'));
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Galeri kaydı güncellendi',
            'Bir galeri kaydı güncellendi.',
            route('admin.galeriler.edit', ['item' => $item->id], absolute: false),
        );

        return redirect()->route('admin.galeriler.index')->with('notification', ['type' => 'success', 'message' => 'Galeri kaydı güncellendi.']);
    }

    public function destroy(GalerilerItem $item, GalerilerItemService $service, Request $request, NotifyService $notifications): RedirectResponse
    {
        $this->authorize('galeriler.delete');
        $service->delete($item);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            'Galeri kaydı silindi',
            'Bir galeri kaydı silindi.',
            route('admin.galeriler.index', absolute: false),
        );

        return redirect()->route('admin.galeriler.index')->with('notification', ['type' => 'success', 'message' => 'Galeri kaydı silindi.']);
    }

    public function bulk(BulkGalerilerItemsRequest $request, GalerilerItemService $service, NotifyService $notifications): RedirectResponse
    {
        $data = $request->validated();
        $service->bulk($data);
        $notifications->sendToAdminsForActor(
            $request->user('admin'),
            $data['action'] === 'delete' ? 'Galeri kayıtları silindi' : 'Galeri kayıtları güncellendi',
            $data['action'] === 'delete' ? 'Toplu işlemde galeri kayıtları silindi.' : 'Toplu işlemde galeri kayıtları güncellendi.',
            route('admin.galeriler.index', absolute: false),
        );

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
