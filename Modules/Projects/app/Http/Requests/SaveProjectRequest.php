<?php

namespace Modules\Projects\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\MediaSelection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
        return [
            'title' => ['required', 'string', 'max:255'],
            'project_category_id' => ['required', 'integer', 'exists:project_categories,id'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('projects', 'slug')->ignore($this->route('project')?->id)],
            'project_date' => ['nullable', 'date_format:Y-m-d'],
            'content' => ['nullable', 'string', 'max:65535'],
            'is_active' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'between:0,65535'],
            'image_media_ids' => ['present', 'list', 'max:100'],
            'image_media_ids.*' => $selection->imageRules(),
            'cover_media_id' => ['nullable', 'integer'],
            ...$values->rules($features->form('Projects', 'project')['fields'], $this->all(), $this->route('project')?->extra_fields ?? []),
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
