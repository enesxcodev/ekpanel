<?php

namespace Modules\Popup\Http\Requests;

use App\Rules\SelectedMediaImage;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePopupRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(ModuleFeatures $features, FeatureValues $values): array
    {
        $popup = $this->route('popup');
        $languageId = $this->input('lang_id');
        $translation = $popup?->translations()->where('lang_id', $languageId)->first();

        return [
            'is_active' => ['sometimes', 'boolean'],
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'max:100000'],
            'starts_at' => ['required', 'date_format:Y-m-d\TH:i'],
            'ends_at' => ['required', 'date_format:Y-m-d\TH:i', 'after:starts_at'],
            'repeat_policy' => ['required', 'in:never_after_close,interval'],
            'repeat_interval' => ['exclude_unless:repeat_policy,interval', 'required', 'integer', 'min:1', 'max:36500'],
            'repeat_unit' => ['exclude_unless:repeat_policy,interval', 'required', 'in:hours,days'],
            'image_media_id' => ['bail', 'nullable', 'integer', new SelectedMediaImage(4096)],
            'image' => ['prohibited'],
            ...$values->rules($features->form('Popup', 'popup')['fields'], $this->all(), $translation?->extra_fields ?? []),
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('popup') ? 'popup.update' : 'popup.create') ?? false;
    }

    public function messages(): array
    {
        return (new FieldRuleBuilder)->messages();
    }

    public function attributes(): array
    {
        return ['is_active' => 'Durum', 'lang_id' => 'Dil', 'title' => 'Başlık', 'content' => 'İçerik', 'image_media_id' => 'Görsel', 'starts_at' => 'Başlangıç', 'ends_at' => 'Bitiş', 'repeat_policy' => 'Tekrar gösterim politikası', 'repeat_interval' => 'Tekrar aralığı', 'repeat_unit' => 'Aralık birimi']
            + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Popup', 'popup')['fields']);
    }
}
