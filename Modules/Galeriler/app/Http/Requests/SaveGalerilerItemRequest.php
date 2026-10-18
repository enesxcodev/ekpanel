<?php

namespace Modules\Galeriler\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaSelection;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Galeriler\Services\VideoLink;

class SaveGalerilerItemRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('item') ? 'galeriler.update' : 'galeriler.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['image_media_ids' => MediaSelection::decode($this->input('image_media_ids'))]);
    }

    public function rules(ModuleFeatures $features, FeatureValues $values, MediaSelection $selection): array
    {
        return [
            'galeriler_category_id' => ['required', 'integer', 'exists:galeriler_categories,id'],
            'type' => ['required', Rule::in(['photo', 'video'])],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'image_media_ids' => ['exclude_unless:type,photo', 'required', 'list', 'min:1', 'max:'.($this->route('item') ? 1 : 100)],
            'image_media_ids.*' => $this->input('type') === 'photo' ? $selection->imageRules() : ['exclude'],
            'video_url' => ['exclude_unless:type,video', 'bail', 'required', 'string', 'max:2048', 'url:https', function (string $attribute, mixed $value, Closure $fail): void {
                if (VideoLink::parse($value) === null) {
                    $fail('Geçerli bir HTTPS YouTube veya Vimeo video bağlantısı girin.');
                }
            }],
            ...$values->rules($features->form('Galeriler', 'item')['fields'], $this->all(), $this->route('item')?->extra_fields ?? []),
        ];
    }

    public function messages(): array
    {
        return ['galeriler_category_id.required' => 'Kategori seçin.', 'image_media_ids.required' => 'En az bir fotoğraf seçin.', 'image_media_ids.max' => 'Düzenlemede tek fotoğraf, eklemede en fazla 100 fotoğraf seçilebilir.', 'video_url.required' => 'Video bağlantısını girin.'];
    }
}
