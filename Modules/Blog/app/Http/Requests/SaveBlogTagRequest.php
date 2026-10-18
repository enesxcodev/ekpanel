<?php

namespace Modules\Blog\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveBlogTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('tag') ? 'blog.tags.update' : 'blog.tags.create') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        $tag = $this->route('tag');

        return ['name' => ['required', 'string', 'max:255', Rule::unique('blog_tags', 'name')->ignore($tag?->id)], 'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('blog_tags', 'slug')->ignore($tag?->id)]];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['name.required' => 'Etiket adını girin.', 'name.unique' => 'Bu etiket adı kullanılıyor.', 'slug.required' => 'Etiket kısa bağlantısını girin.', 'slug.unique' => 'Bu kısa bağlantı kullanılıyor.', 'slug.regex' => 'Kısa bağlantı yalnızca küçük harf, rakam ve tire içerebilir.'];
    }
}
