<?php

namespace Modules\Galeriler\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\ContentMediaService;
use App\Services\Media\MediaSelection;
use App\Services\Media\MediaService;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Modules\Galeriler\Models\GalerilerCategory;
use Modules\Galeriler\Models\GalerilerItem;

class GalerilerItemService
{
    public function __construct(private FieldFiles $files, private ModuleFeatures $features, private FeatureValues $values, private MediaService $media, private ContentMediaService $contentMedia, private MediaSelection $selection) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?GalerilerItem $item, Admin $actor): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($data, $item, $actor): void {
            $category = GalerilerCategory::query()->lockForUpdate()->findOrFail($data['galeriler_category_id']);
            $item = $item ? GalerilerItem::query()->lockForUpdate()->findOrFail($item->id) : null;
            $imageIds = $data['type'] === 'photo' ? $data['image_media_ids'] : [];
            $this->selection->images($imageIds, $actor, $item?->image_media_id ? [$item->image_media_id] : []);
            $nextOrder = (int) $category->items()->max('sort_order') + 1;
            foreach ($data['type'] === 'photo' ? $imageIds : [null] as $offset => $imageId) {
                $record = $item ?? new GalerilerItem;
                $record->fill([
                    'galeriler_category_id' => $category->id, 'type' => $data['type'],
                    'title' => $data['title'] ?? null, 'description' => $data['description'] ?? null,
                    'video_url' => $data['type'] === 'video' ? $data['video_url'] : null,
                    ...$this->contentMedia->prepare($record, ['image_media_id' => $imageId], $this->contentMedia->definition(), $actor),
                    'sort_order' => $item && $item->galeriler_category_id === $category->id ? $item->sort_order : $nextOrder + $offset,
                ])->save();
                $definitions = $this->features->form('Galeriler', 'item')['fields'];
                $record->extra_fields = $this->values->prepare($definitions, $data, $record->extra_fields ?? [], 'galeriler/'.$record->id.'/features', $files, $actor);
                $record->save();
                $this->contentMedia->syncUsage($record, 'image', 'Galeri / '.($record->title ?? $record->id), 'admin.galeriler.edit');
                $this->values->syncMediaUsages($definitions, $record, $this->media, 'Galeri / '.$record->id, 'admin.galeriler.edit');
            }
        });
    }

    public function delete(GalerilerItem $item): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($item): void {
            $item = GalerilerItem::query()->lockForUpdate()->findOrFail($item->id);
            $this->remove($item, $files);
        });
    }

    private function remove(GalerilerItem $item, FieldFiles $files): void
    {
        foreach ($this->features->form('Galeriler', 'item')['fields'] as $key => $definition) {
            if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                $files->replace(null, $item->extra_fields[$key] ?? null, 'galeriler/'.$item->id.'/features', true);
            }
        }
        $this->media->detachUsages($item);
        $item->delete();
    }

    /** @param array<string, mixed> $data */
    public function bulk(array $data): void
    {
        if ($data['action'] === 'delete') {
            $this->files->transaction(function (FieldFiles $files) use ($data): void {
                foreach (GalerilerItem::query()->whereKey($data['ids'])->orderBy('id')->lockForUpdate()->get() as $item) {
                    $this->remove($item, $files);
                }
            });

            return;
        }
        DB::transaction(function () use ($data): void {
            $category = GalerilerCategory::query()->lockForUpdate()->findOrFail($data['galeriler_category_id']);
            $next = (int) $category->items()->max('sort_order') + 1;
            foreach (GalerilerItem::query()->whereKey($data['ids'])->orderBy('id')->lockForUpdate()->get() as $item) {
                $item->update(['galeriler_category_id' => $category->id, 'sort_order' => $next++]);
            }
        });
    }

    /** @param list<int|string> $ids */
    public function reorder(int $categoryId, array $ids): void
    {
        DB::transaction(function () use ($categoryId, $ids): void {
            GalerilerCategory::query()->lockForUpdate()->findOrFail($categoryId);
            $items = GalerilerItem::query()->where('galeriler_category_id', $categoryId)->orderBy('sort_order')->orderBy('id')->lockForUpdate()->get();
            $ids = array_map(intval(...), $ids);
            if (array_diff($ids, $items->modelKeys()) !== []) {
                throw ValidationException::withMessages(['ids' => 'Yalnızca seçili kategorinin kayıtları sıralanabilir.']);
            }
            $ordered = $items->modelKeys();
            $replacement = $ids;
            foreach ($ordered as $index => $id) {
                if (in_array($id, $ids, true)) {
                    $ordered[$index] = array_shift($replacement);
                }
            }
            foreach ($ordered as $index => $id) {
                $items->find($id)->update(['sort_order' => $index + 1]);
            }
        });
    }
}
