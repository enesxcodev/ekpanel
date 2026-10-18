<?php

namespace Modules\Galeriler\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;

class SaveGalerilerCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('category') ? 'galeriler.categories.update' : 'galeriler.categories.create') ?? false;
    }

    public function rules(ModuleFeatures $features, FeatureValues $values): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'],
            'parent_id' => ['nullable', 'integer', 'exists:galeriler_categories,id'],
            'short_description' => ['nullable', 'string', 'max:500'],
            ...$values->rules($features->form('Galeriler', 'category')['fields'], $this->all(), $this->route('category')?->extra_fields ?? []),
        ];
    }

    public function messages(): array
    {
        return ['name.required' => 'Kategori adını girin.', 'slug.unique' => 'Bu kısa bağlantı zaten kullanılıyor.'];
    }
}
