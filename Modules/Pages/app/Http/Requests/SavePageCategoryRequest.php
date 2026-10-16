<?php

namespace Modules\Pages\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Pages\Models\PageCategory;

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
        $slugRule = Rule::unique('page_categories', 'slug')->where(function (Builder $query) use ($parentId): void {
            if ($parentId === null || $parentId === '') {
                $query->whereNull('parent_id');
            } else {
                $query->where('parent_id', $parentId);
            }
        });
        if ($category !== null) {
            $slugRule->ignore($category->id);
        }

        return [
            'parent_id' => ['nullable', 'integer', 'exists:page_categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slugRule],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            ...$values->rules($features->form('Pages', 'category')['fields'], $this->all(), $category?->extra_fields ?? []),
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $category = $this->route('category');
            $parentId = $this->input('parent_id');
            if (! $category instanceof PageCategory || $parentId === null || $parentId === '') {
                return;
            }

            if ((int) $parentId === $category->id || in_array((int) $parentId, $category->descendantIds(), true)) {
                $validator->errors()->add('parent_id', 'Kategori kendisinin veya alt kategorilerinden birinin üst kategorisi olamaz.');
            }
        }];
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
