<?php

namespace Modules\Projects\Http\Requests;

use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaSelection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Projects\Services\ProjectsLanguageService;

class SaveProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('project') ? 'projects.update' : 'projects.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['image_media_ids' => MediaSelection::decode($this->input('image_media_ids'))]);
    }

    public function rules(ModuleFeatures $features, FeatureValues $values, MediaSelection $selection): array
    {
        $project = $this->route('project');
        $language = Language::query()->where('is_active', true)->find($this->input('lang_id'));
        $isPrimary = $language?->code === ProjectsLanguageService::PrimaryCode;
        $translation = $project?->translations()->where('lang_id', $language?->id)->first();
        $sharedAllowed = ! $project || $isPrimary;

        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true), function (string $attribute, mixed $value, \Closure $fail) use ($project): void {
                if (! $project && Language::query()->whereKey($value)->value('code') !== ProjectsLanguageService::PrimaryCode) {
                    $fail('Yeni projeler Türkçe oluşturulmalıdır.');
                }
            }],
            'title' => ['required', 'string', 'max:255'],
            'project_category_id' => $sharedAllowed ? ['required', 'integer', 'exists:project_categories,id'] : ['prohibited'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('project_translations', 'slug')->where('lang_id', $language?->id)->ignore($translation?->id)],
            'project_date' => $sharedAllowed ? ['nullable', 'date_format:Y-m-d'] : ['prohibited'],
            'content' => ['nullable', 'string', 'max:65535'],
            'is_active' => $sharedAllowed ? ['required', 'boolean'] : ['prohibited'],
            'sort_order' => $sharedAllowed ? ['required', 'integer', 'between:0,65535'] : ['prohibited'],
            'image_media_ids' => $sharedAllowed ? ['present', 'list', 'max:100'] : ['prohibited'],
            'image_media_ids.*' => $sharedAllowed ? $selection->imageRules() : ['prohibited'],
            'cover_media_id' => $sharedAllowed ? ['nullable', 'integer'] : ['prohibited'],
            ...$values->rules($features->form('Projects', 'project')['fields'], $this->all(), $translation?->extra_fields ?? []),
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isEmpty() && $this->filled('cover_media_id') && ! in_array($this->integer('cover_media_id'), array_map(intval(...), $this->input('image_media_ids', [])), true)) {
                $validator->errors()->add('cover_media_id', 'Kapak, seçilen proje görsellerinden biri olmalıdır.');
            }
        }];
    }

    public function messages(): array
    {
        return ['title.required' => 'Proje başlığını girin.', 'slug.unique' => 'Bu kısa bağlantı zaten kullanılıyor.', 'project_category_id.required' => 'Kategori seçin.'];
    }
}
