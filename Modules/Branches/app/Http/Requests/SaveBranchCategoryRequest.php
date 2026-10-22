<?php

namespace Modules\Branches\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SaveBranchCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('category') === null ? 'branches.categories.create' : 'branches.categories.update';

        return $this->user('admin')?->can($permission) ?? false;
    }

    protected function prepareForValidation(): void
    {
        $slug = $this->input('slug');
        $name = $this->input('name');
        if (blank($slug) && is_string($name)) {
            $this->merge(['slug' => Str::slug($name)]);
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('branch_categories', 'slug')->ignore($this->route('category'))],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['name' => 'Kategori adı', 'slug' => 'Slug', 'sort_order' => 'Sıralama', 'is_active' => 'Durum'];
    }
}
