<?php

namespace Modules\Pages\Http\Requests;

use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Pages\Models\PageCategory;
use Modules\Pages\Models\PageCategoryTranslation;

class SavePageCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('category') ? 'pages.categories.update' : 'pages.categories.create';

        return $this->user('admin')?->can($permission) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(ModuleFeatures $features, FeatureValues $values): array
    {
        $category = $this->route('category');
        $parentId = $this->input('parent_id');
        $languageId = $this->input('lang_id');
        $translation = $category?->translations()->where('lang_id', $languageId)->first();
        $scopeId = $parentId ?: 0;

        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)],
            'parent_id' => ['nullable', 'integer', 'exists:page_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('page_category_translations', 'slug')->where('lang_id', $languageId)->where('scope_id', $scopeId)->ignore($translation?->id)],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            ...$values->rules($features->form('Pages', 'category')['fields'], $this->all(), $translation?->extra_fields ?? []),
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $category = $this->route('category');
            $parentId = $this->input('parent_id');
            if ($validator->errors()->has('lang_id')) {
                return;
            }

            if ($parentId !== null && $parentId !== '' && ! PageCategory::query()->find($parentId)?->translations()->where('lang_id', $this->input('lang_id'))->exists()) {
                $validator->errors()->add('parent_id', 'Üst kategori seçilen dilde çevrilmiş olmalıdır.');
            }

            if ($category instanceof PageCategory) {
                foreach ($category->translations as $existingTranslation) {
                    $conflict = PageCategoryTranslation::query()->where('scope_id', $parentId ?: 0)
                        ->where('lang_id', $existingTranslation->lang_id)->where('slug', $existingTranslation->slug)
                        ->where('page_category_id', '!=', $category->id)->exists();
                    if ($conflict) {
                        $validator->errors()->add('parent_id', 'Üst kategori değişikliği, başka bir dildeki kategori kısa bağlantısıyla çakışıyor.');

                        return;
                    }
                }
            }

            if (! $category instanceof PageCategory || $parentId === null || $parentId === '') {
                return;
            }

            if ((int) $parentId === $category->id || in_array((int) $parentId, $category->descendantIds(), true)) {
                $validator->errors()->add('parent_id', 'Kategori kendisinin veya alt kategorilerinden birinin üst kategorisi olamaz.');
            }
        }];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->exists('lang_id')) {
            $this->merge(['lang_id' => Language::query()->where('code', 'tr')->where('is_active', true)->value('id')]);
        }
    }

    /** @return array<string, string|array<string, string>> */
    public function messages(): array
    {
        return [
            'name.required' => 'Kategori adını girin.',
            'slug.required' => 'Kategori kısa bağlantısını girin.',
            'slug.regex' => 'Kısa bağlantı yalnızca küçük harf, rakam ve tire içerebilir.',
            'slug.unique' => 'Bu seviyede aynı kısa bağlantı zaten kullanılıyor.',
            'parent_id.exists' => 'Geçerli bir üst kategori seçin.',
        ] + (new FieldRuleBuilder)->messages();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'parent_id' => 'Üst kategori',
            'name' => 'Kategori adı',
            'short_description' => 'Kısa açıklama',
            'slug' => 'Kısa bağlantı',
            'sort_order' => 'Sıralama',
        ] + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Pages', 'category')['fields']);
    }
}
