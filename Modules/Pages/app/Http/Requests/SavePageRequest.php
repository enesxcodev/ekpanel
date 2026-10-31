<?php

namespace Modules\Pages\Http\Requests;

use App\Models\Settings\Language;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\ContentMediaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;
use Modules\Pages\Models\PageCategory;
use Modules\Pages\Models\PageTranslation;

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
        $languageId = $this->input('lang_id');
        $translation = $page?->translations()->where('lang_id', $languageId)->first();
        $scopeId = $this->input('page_category_id') ?: 0;

        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)],
            'page_category_id' => ['nullable', 'integer', 'exists:page_categories,id'],
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_keywords' => ['nullable', 'string', 'max:1000'],
            'meta_description' => ['nullable', 'string', 'max:1000'],
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:500'],
            'content' => ['nullable', 'string', 'max:65535'],
            ...$media->rules($media->definition()),
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('page_translations', 'slug')->where('lang_id', $languageId)->where('scope_id', $scopeId)->ignore($translation?->id)],
            'status' => ['required', Rule::in(['draft', 'published'])],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            ...$values->rules($features->form('Pages', 'page')['fields'], $this->all(), $translation?->extra_fields ?? []),
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->exists('lang_id')) {
            $this->merge(['lang_id' => Language::query()->where('code', 'tr')->where('is_active', true)->value('id')]);
        }

        if ($this->route('page') === null && ! $this->exists('status')) {
            $this->merge(['status' => 'published']);
        }
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $page = $this->route('page');
            if ($validator->errors()->has('lang_id')) {
                return;
            }

            if ($this->filled('page_category_id')) {
                $category = PageCategory::query()->find($this->integer('page_category_id'));
                $languageId = $this->integer('lang_id');
                while ($category !== null) {
                    if (! $category->translations()->where('lang_id', $languageId)->exists()) {
                        $validator->errors()->add('page_category_id', 'Seçilen dilde kategori ve tüm üst kategoriler çevrilmiş olmalıdır.');

                        return;
                    }
                    $category = $category->parent;
                }
            }

            if ($page !== null) {
                foreach ($page->translations as $existingTranslation) {
                    $conflict = PageTranslation::query()->where('scope_id', $this->input('page_category_id') ?: 0)
                        ->where('lang_id', $existingTranslation->lang_id)->where('slug', $existingTranslation->slug)
                        ->where('page_id', '!=', $page->id)->exists();
                    if ($conflict) {
                        $validator->errors()->add('page_category_id', 'Kategori değişikliği, başka bir dildeki sayfa kısa bağlantısıyla çakışıyor.');

                        return;
                    }
                }
            }
        }];
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
