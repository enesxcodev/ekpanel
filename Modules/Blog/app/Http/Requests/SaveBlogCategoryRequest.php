<?php

namespace Modules\Blog\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Blog\Models\BlogCategory;

class SaveBlogCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('category') ? 'blog.categories.update' : 'blog.categories.create') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(ModuleFeatures $features, FeatureValues $values): array
    {
        $category = $this->route('category');
        $translation = $category?->translations()->where('lang_id', $this->input('lang_id'))->first();
        $slug = Rule::unique('blog_category_translations', 'slug')->where('lang_id', $this->input('lang_id'))->ignore($translation?->id);

        return ['lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)], 'parent_id' => ['nullable', 'integer', 'exists:blog_categories,id'], 'name' => ['required', 'string', 'max:255'], 'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $slug], 'sort_order' => ['required', 'integer', 'min:0', 'max:65535'], ...$values->rules($features->form('Blog', 'category')['fields'], $this->all(), $translation?->extra_fields ?? [])];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $category = $this->route('category');
            $parentId = $this->input('parent_id');
            if (! $category instanceof BlogCategory || $parentId === null || $parentId === '') {
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
        return ['name.required' => 'Kategori adını girin.', 'slug.required' => 'Kategori kısa bağlantısını girin.', 'slug.unique' => 'Bu seviyede aynı kısa bağlantı zaten kullanılıyor.', 'slug.regex' => 'Kısa bağlantı yalnızca küçük harf, rakam ve tire içerebilir.'] + (new FieldRuleBuilder)->messages();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['parent_id' => 'Üst kategori', 'name' => 'Kategori adı', 'slug' => 'Kısa bağlantı', 'sort_order' => 'Sıralama'] + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Blog', 'category')['fields']);
    }
}
