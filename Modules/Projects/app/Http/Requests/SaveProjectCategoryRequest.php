<?php

namespace Modules\Projects\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveProjectCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('category') ? 'projects.categories.update' : 'projects.categories.create') ?? false;
    }

    public function rules(ModuleFeatures $features, FeatureValues $values): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('project_categories', 'slug')->ignore($this->route('category')?->id)],
            ...$values->rules($features->form('Projects', 'category')['fields'], $this->all(), $this->route('category')?->extra_fields ?? []),
        ];
    }

    public function messages(): array
    {
        return ['name.required' => 'Kategori adını girin.', 'slug.unique' => 'Bu kısa bağlantı zaten kullanılıyor.'];
    }
}
