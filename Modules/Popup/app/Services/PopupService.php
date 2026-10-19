<?php

namespace Modules\Popup\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Media\MediaUsage;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\HtmlSanitizer;
use App\Services\Media\MediaService;
use Modules\Popup\Models\Popup;

class PopupService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private MediaService $media, private HtmlSanitizer $sanitizer) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, Admin $actor, ?Popup $popup = null): Popup
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $actor, $popup): Popup {
            $popup = $popup === null ? new Popup : Popup::query()->lockForUpdate()->findOrFail($popup->id);
            $mediaId = array_key_exists('image_media_id', $data) ? $data['image_media_id'] : $popup->image_media_id;
            if ($mediaId !== null && (int) $mediaId !== $popup->image_media_id) {
                abort_unless($actor->can('media.view'), 403, 'Medya kütüphanesinden seçim yapma izniniz bulunmuyor.');
            }
            $image = $mediaId === null ? null : MediaFile::query()->where('kind', 'image')->lockForUpdate()->findOrFail($mediaId);
            $definitions = $this->features->form('Popup', 'popup')['fields'];
            $extraFields = $this->values->prepare($definitions, $data, $popup->extra_fields ?? [], 'popup/features', $files, $actor);
            $popup->fill([
                'is_active' => $data['is_active'] ?? $popup->is_active ?? true,
                'title' => $data['title'], 'content' => $this->sanitizer->sanitize($data['content']),
                'starts_at' => $data['starts_at'], 'ends_at' => $data['ends_at'],
                'repeat_policy' => $data['repeat_policy'],
                'repeat_interval' => $data['repeat_policy'] === 'interval' ? $data['repeat_interval'] : null,
                'repeat_unit' => $data['repeat_policy'] === 'interval' ? $data['repeat_unit'] : null,
                'image_media_id' => $image?->id,
                'extra_fields' => $extraFields,
            ])->save();
            if ($image !== null) {
                $this->media->attachUsage($image, $popup, 'image', 'Popup / '.$popup->title, 'admin.popup.edit');
            } else {
                MediaUsage::query()->where('owner_type', $popup->getMorphClass())->where('owner_id', $popup->getKey())->where('field', 'image')->delete();
            }
            $this->values->syncMediaUsages($definitions, $popup, $this->media, 'Popup / '.$popup->title, 'admin.popup.edit');

            return $popup;
        });
    }

    public function delete(Popup $popup): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($popup): void {
            $popup = Popup::query()->lockForUpdate()->findOrFail($popup->id);
            $this->media->detachUsages($popup);
            foreach ($this->features->form('Popup', 'popup')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    $files->replace(null, $popup->extra_fields[$key] ?? null, 'popup/features', true);
                }
            }
            $popup->delete();
        });
    }
}
