<?php

namespace Modules\Pages\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\ContentMediaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePageRequest extends FormRequest
{
    public function authorize(): bool
    {
        $permission = $this->route('page') ? 'pages.update' : 'pages.create';

        return $this->user('admin')?->can($permission) ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(ModuleFeatures $features, FeatureValues $values, ContentMediaService $media): array
    {
        $page = $this->route('page');
        $uniqueSlug = Rule::unique('pages', 'slug')->ignore($page?->id);
        $uniqueSlug = $this->filled('page_category_id')
            ? $uniqueSlug->where('page_category_id', $this->input('page_category_id'))
            : $uniqueSlug->whereNull('page_category_id');

        return [
            'page_category_id' => ['nullable', 'integer', 'exists:page_categories,id'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:1000'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string', 'max:65535'],
            ...$media->rules($media->definition()),
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', $uniqueSlug],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            ...$values->rules($features->form('Pages', 'page')['fields'], $this->all(), $page?->extra_fields ?? []),
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->route('page') === null && ! $this->exists('status')) {
            $this->merge(['status' => 'published']);
        }
    }

    /** @return array<string, string|array<string, string>> */
    public function messages(): array
    {
        return [
            'title.required' => 'Sayfa başlığını girin.',
            'slug.required' => 'Sayfa kısa bağlantısını girin.',
            'slug.regex' => 'Kısa bağlantı yalnızca küçük harf, rakam ve tire içerebilir.',
            'slug.unique' => 'Bu kategoride aynı kısa bağlantı zaten kullanılıyor.',
            'status.in' => 'Sayfa durumu taslak veya yayında olmalıdır.',
            'image.image' => 'Geçerli bir resim dosyası seçin.',
            'image.mimes' => 'Resim JPG, PNG veya WebP olmalıdır.',
            'image.max' => 'Resim en fazla 4 MB olabilir.',
        ] + (new FieldRuleBuilder)->messages();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'page_category_id' => 'Sayfa kategorisi',
            'meta_title' => 'Meta başlık',
            'meta_keywords' => 'Meta anahtar',
            'meta_description' => 'Meta açıklama',
            'title' => 'Başlık',
            'short_description' => 'Kısa açıklama',
            'content' => 'İçerik',
            'image_media_id' => 'Resim',
            'slug' => 'Kısa bağlantı',
            'status' => 'Yayın durumu',
            'sort_order' => 'Sıralama',
        ] + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Pages', 'page')['fields']);
    }
}
