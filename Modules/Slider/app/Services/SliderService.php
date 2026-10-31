<?php

namespace Modules\Slider\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use App\Models\Media\MediaUsage;
use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaService;
use Modules\Slider\Models\Slider;

class SliderService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private MediaService $media) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, Admin $actor, ?Slider $slider = null): Slider
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $actor, $slider): Slider {
            $slider = $slider === null ? new Slider : Slider::query()->lockForUpdate()->findOrFail($slider->id);
            $mediaId = $data['image_media_id'] ?? $slider->image_media_id;
            if ($mediaId !== null && (int) $mediaId !== $slider->image_media_id) {
                abort_unless($actor->can('media.view'), 403, 'Medya kütüphanesinden seçim yapma izniniz bulunmuyor.');
            }
            $image = $mediaId === null ? null : MediaFile::query()->where('kind', 'image')->lockForUpdate()->findOrFail($mediaId);
            $language = Language::query()->where('is_active', true)->findOrFail($data['lang_id']);
            $definitions = $this->features->form('Slider', 'slider')['fields'];
            $slider->fill([
                'is_active' => $data['is_active'] ?? $slider->is_active ?? true,
                'image_media_id' => $image?->id,
                'image' => $image?->path ?? $slider->image,
            ])->save();
            $translation = $slider->translations()->firstOrNew(['lang_id' => $language->id]);
            $extraFields = $this->values->prepare($definitions, $data, $translation->extra_fields ?? [], 'slider/'.$slider->id.'/translations/'.$language->id.'/features', $files, $actor);
            $translation->fill(['title' => $data['title'], 'subtitle' => $data['subtitle'] ?? null, 'extra_fields' => $extraFields])->save();
            if ($image !== null) {
                $this->media->attachUsage($image, $slider, 'image', 'Slider / '.$translation->title, 'admin.slider.edit');
            } else {
                MediaUsage::query()->where('owner_type', $slider->getMorphClass())->where('owner_id', $slider->getKey())->where('field', 'image')->delete();
            }
            $this->values->syncMediaUsages($definitions, $translation, $this->media, 'Slider / '.$translation->title, 'admin.slider.edit');
            $slider->setRelation('selectedTranslation', $translation);

            return $slider;
        });
    }

    public function delete(Slider $slider): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($slider): void {
            $slider = Slider::query()->lockForUpdate()->findOrFail($slider->id);
            $this->media->detachUsages($slider);
            foreach ($slider->translations()->get() as $translation) {
                foreach ($this->features->form('Slider', 'slider')['fields'] as $key => $definition) {
                    if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                        $files->replace(null, $translation->extra_fields[$key] ?? null, 'slider/'.$slider->id.'/translations/'.$translation->lang_id.'/features', true);
                    }
                }
                $this->media->detachUsages($translation);
            }
            $slider->delete();
        });
    }
}
