<?php

namespace Modules\Slider\Http\Requests;

use App\Rules\SelectedMediaImage;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;

class SaveSliderRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(ModuleFeatures $features, FeatureValues $values): array
    {
        $slider = $this->route('slider');

        return [
            'is_active' => ['sometimes', 'boolean'],
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:10000'],
            'image_media_id' => ['bail', $slider && ! $slider->image_media_id && $slider->image ? 'nullable' : 'required', 'integer', new SelectedMediaImage(4096)],
            'image' => ['prohibited'],
            ...$values->rules($features->form('Slider', 'slider')['fields'], $this->all(), $slider?->extra_fields ?? []),
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('slider') ? 'slider.update' : 'slider.create') ?? false;
    }

    public function messages(): array
    {
        return (new FieldRuleBuilder)->messages();
    }

    public function attributes(): array
    {
        return ['is_active' => 'Durum', 'title' => 'Başlık', 'subtitle' => 'Alt başlık', 'image_media_id' => 'Görsel']
            + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Slider', 'slider')['fields']);
    }
}
