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
        if ($this->route('category') === null && blank($this->input('slug')) && is_string($this->input('name'))) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $category = $this->route('category');

        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)],
            'name' => ['required', 'string', 'max:150'],
            'slug' => $category === null ? ['required', 'string', 'max:80', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('branch_categories', 'slug')] : ['prohibited'],
            'sort_order' => $category === null ? ['required', 'integer', 'min:0', 'max:65535'] : ['prohibited'],
            'is_active' => $category === null ? ['required', 'boolean'] : ['prohibited'],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['lang_id' => 'Dil', 'name' => 'Kategori adı', 'slug' => 'Slug', 'sort_order' => 'Sıralama', 'is_active' => 'Durum'];
    }
}
