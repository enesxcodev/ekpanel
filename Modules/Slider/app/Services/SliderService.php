<?php

namespace Modules\Slider\Services;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use Modules\Slider\Models\Slider;

class SliderService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Slider $slider = null): Slider
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $slider): Slider {
            $slider = $slider === null ? new Slider : Slider::query()->lockForUpdate()->findOrFail($slider->id);
            $extraFields = $this->values->prepare($this->features->form('Slider', 'slider')['fields'], $data, $slider->extra_fields ?? [], 'slider/features', $files);
            $slider->fill([
                'title' => $data['title'], 'subtitle' => $data['subtitle'] ?? null,
                'image' => $files->replace($data['image'] ?? null, $slider->image, 'slider/images'),
                'extra_fields' => $extraFields,
            ])->save();

            return $slider;
        });
    }

    public function delete(Slider $slider): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($slider): void {
            $slider = Slider::query()->lockForUpdate()->findOrFail($slider->id);
            $files->replace(null, $slider->image, 'slider/images', true);
            foreach ($this->features->form('Slider', 'slider')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile()) {
                    $files->replace(null, $slider->extra_fields[$key] ?? null, 'slider/features', true);
                }
            }
            $slider->delete();
        });
    }
}
