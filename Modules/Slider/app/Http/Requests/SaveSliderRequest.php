<?php

namespace Modules\Slider\Http\Requests;

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
            'title' => ['required', 'string', 'max:255'],
            'subtitle' => ['nullable', 'string', 'max:10000'],
            'image' => [$slider?->image ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:4096'],
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
        return ['title' => 'Başlık', 'subtitle' => 'Alt başlık', 'image' => 'Görsel']
            + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Slider', 'slider')['fields']);
    }
}
