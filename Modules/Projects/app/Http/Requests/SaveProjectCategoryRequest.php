<?php

namespace Modules\Projects\Http\Requests;

use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Projects\Services\ProjectsLanguageService;

class SaveProjectCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('category') ? 'projects.categories.update' : 'projects.categories.create') ?? false;
    }

    public function rules(ModuleFeatures $features, FeatureValues $values): array
    {
        $category = $this->route('category');
        $language = Language::query()->where('is_active', true)->find($this->input('lang_id'));
        $isPrimary = $language?->code === ProjectsLanguageService::PrimaryCode;
        $translation = $category?->translations()->where('lang_id', $language?->id)->first();
        $sharedAllowed = ! $category || $isPrimary;

        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true), function (string $attribute, mixed $value, \Closure $fail) use ($category): void {
                if (! $category && Language::query()->whereKey($value)->value('code') !== ProjectsLanguageService::PrimaryCode) {
                    $fail('Yeni proje kategorileri Türkçe oluşturulmalıdır.');
                }
            }],
            'name' => ['required', 'string', 'max:255'],
            'icon' => $sharedAllowed ? ['nullable', 'string', 'max:100', 'regex:/^[a-z0-9-]+$/'] : ['prohibited'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('project_category_translations', 'slug')->where('lang_id', $language?->id)->ignore($translation?->id)],
            ...$values->rules($features->form('Projects', 'category')['fields'], $this->all(), $translation?->extra_fields ?? []),
        ];
    }

    public function messages(): array
    {
        return ['name.required' => 'Kategori adını girin.', 'slug.unique' => 'Bu kısa bağlantı zaten kullanılıyor.'];
    }
}
