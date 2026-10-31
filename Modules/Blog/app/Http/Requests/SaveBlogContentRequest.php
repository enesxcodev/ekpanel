<?php

namespace Modules\Blog\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\ContentMediaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveBlogContentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('blog') ? 'blog.update' : 'blog.create') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(ModuleFeatures $features, FeatureValues $values, ContentMediaService $media): array
    {
        $content = $this->route('blog');
        $translation = $content?->translations()->where('lang_id', $this->input('lang_id'))->first();

        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)],
            'blog_category_id' => ['required', 'integer', 'exists:blog_categories,id'],
            'author_id' => ['nullable', 'integer', 'exists:admins,id'],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('blog_content_translations', 'slug')->where('lang_id', $this->input('lang_id'))->ignore($translation?->id)],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string', 'max:65535'],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['integer', 'distinct', 'exists:blog_tags,id'],
            'status' => ['required', Rule::in(['draft', 'published', 'archived'])],
            'published_at' => ['nullable', 'date', 'before_or_equal:now'],
            ...$media->rules($media->definition()),
            ...$values->rules($features->form('Blog', 'content')['fields'], $this->all(), $translation?->extra_fields ?? []),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('blog') === null && ! $this->exists('status')) {
            $this->merge(['status' => 'draft']);
        }
    }

    /** @return array<string, string|array<string, string>> */
    public function messages(): array
    {
        return ['title.required' => 'İçerik başlığını girin.', 'slug.required' => 'Kısa bağlantıyı girin.', 'slug.unique' => 'Bu kısa bağlantı kullanılıyor.', 'slug.regex' => 'Kısa bağlantı yalnızca küçük harf, rakam ve tire içerebilir.', 'blog_category_id.required' => 'Bir kategori seçin.', 'status.in' => 'İçerik durumu taslak, yayında veya arşiv olmalıdır.', 'published_at.before_or_equal' => 'İleri tarihli yayınlama yapılamaz.'] + (new FieldRuleBuilder)->messages();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['blog_category_id' => 'Kategori', 'author_id' => 'Yazar', 'title' => 'Başlık', 'slug' => 'Kısa bağlantı', 'meta_title' => 'Meta başlık', 'meta_description' => 'Meta açıklama', 'short_description' => 'Kısa açıklama', 'content' => 'İçerik', 'tags' => 'Etiketler', 'status' => 'Durum', 'published_at' => 'Yayın tarihi', 'image_media_id' => 'Öne çıkan görsel'] + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Blog', 'content')['fields']);
    }
}
