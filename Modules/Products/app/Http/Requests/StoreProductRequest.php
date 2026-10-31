<?php

namespace Modules\Products\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldRuleBuilder;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\ContentMediaService;
use App\Services\Media\MediaSelection;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('products.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->input('slug') ?: Str::slug(is_string($this->input('title')) ? $this->input('title') : '', language: 'tr'),
            'image_media_ids' => MediaSelection::decode($this->input('image_media_ids')),
            'is_active' => $this->input('is_active', true),
            'sort_order' => $this->input('sort_order') ?? 0,
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(ModuleFeatures $features, FeatureValues $values, ContentMediaService $media): array
    {
        $product = $this->route('product');
        $extraFields = $product?->translations()->where('lang_id', $this->input('lang_id'))->value('extra_fields');
        $existingExtraFields = is_string($extraFields) ? json_decode($extraFields, true) : [];

        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('product_translations', 'slug')->where('lang_id', $this->input('lang_id'))->ignore($product?->translations()->where('lang_id', $this->input('lang_id'))->value('id'))],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'title' => ['required', 'string', 'max:255'],
            'product_category_id' => ['nullable', 'integer', 'exists:product_categories,id'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string', 'max:65535'],
            'site_form_id' => ['nullable', 'integer', 'exists:site_forms,id'],
            'image_media_ids' => ['present', 'list', 'max:100'],
            'image_media_ids.*' => app(MediaSelection::class)->imageRules(),
            ...$media->rules($media->definition()),
            'is_active' => ['required', 'boolean'],
            ...$values->rules($features->form('Products', 'product')['fields'], $this->all(), is_array($existingExtraFields) ? $existingExtraFields : []),
        ];
    }

    /** @return array<string, string|array<string, string>> */
    public function messages(): array
    {
        return [
            'title.required' => 'Ürün başlığını girin.',
            'product_category_id.exists' => 'Geçerli bir ürün kategorisi seçin.',
        ] + (new FieldRuleBuilder)->messages();
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['slug' => 'Kısa bağlantı', 'sort_order' => 'Sıralama', 'title' => 'Ürün adı', 'product_category_id' => 'Kategori', 'short_description' => 'Ürün kısa içerik', 'content' => 'Ürün içerik', 'image_media_id' => 'Ürün resim', 'is_active' => 'Ürün durum']
            + app(FeatureValues::class)->labels(app(ModuleFeatures::class)->form('Products', 'product')['fields']);
    }
}
