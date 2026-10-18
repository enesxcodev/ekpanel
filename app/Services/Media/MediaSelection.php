<?php

namespace App\Services\Media;

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Rules\SelectedFeatureMedia;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Validator;
use JsonException;

class MediaSelection
{
    public function __construct(private ContentMediaService $contentMedia) {}

    public static function decode(mixed $value): mixed
    {
        if ($value === null || $value === '') {
            return [];
        }
        if (! is_string($value)) {
            return $value;
        }
        try {
            return json_decode($value, true, 32, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return $value;
        }
    }

    /** @return list<mixed> */
    public function imageRules(): array
    {
        return ['bail', 'required', 'integer', 'distinct', new SelectedFeatureMedia($this->contentMedia->definition())];
    }

    /** @param list<int|string> $ids @param list<int> $existingIds @return Collection<int, MediaFile> */
    public function images(array $ids, Admin $actor, array $existingIds = []): Collection
    {
        $ids = array_map(intval(...), $ids);
        if (array_diff($ids, $existingIds) !== []) {
            abort_unless($actor->can('media.view'), 403);
        }
        $files = MediaFile::query()->where('disk', 'public')->whereKey($ids)->lockForUpdate()->get()->keyBy('id');
        Validator::make(['image_media_ids' => $ids], [
            'image_media_ids' => ['present', 'list', 'max:100'],
            'image_media_ids.*' => $this->imageRules(),
        ])->validate();

        return $files;
    }
}
