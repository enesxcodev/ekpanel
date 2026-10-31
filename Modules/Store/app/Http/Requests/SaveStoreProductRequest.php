<?php

namespace Modules\Store\Http\Requests;

use App\Services\Fields\FeatureValues;
use App\Services\Fields\ModuleFeatures;
use App\Services\Media\ContentMediaService;
use App\Services\Media\MediaSelection;
use App\Services\Settings\SettingsService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Modules\Store\Services\StoreLanguageService;
use Modules\Store\Services\StoreMoney;

class SaveStoreProductRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'slug' => $this->input('slug') ?: Str::slug(is_string($this->input('title')) ? $this->input('title') : '', language: 'tr'),
            'lang_id' => $this->input('lang_id') ?: app(StoreLanguageService::class)->forAdmin(request()->query('lang'))?->id,
            'currency' => app(SettingsService::class)->get('store.currency', 'TRY'),
            'image_media_ids' => MediaSelection::decode($this->input('image_media_ids')),
        ]);
    }

    /** @return array<string, mixed> */
    public function rules(ContentMediaService $media, ModuleFeatures $features, FeatureValues $values): array
    {
        $product = $this->route('product');
        $languageId = $this->input('lang_id');
        $amount = ['nullable', 'regex:/^\d{1,9}([.,]\d{1,2})?$/D'];

        return [
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)],
            'title' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/D', Rule::unique('store_product_translations', 'slug')->where('lang_id', $languageId)->ignore($product?->translations()->where('lang_id', $languageId)->value('id'))],
            'sku' => ['required', 'string', 'max:100', Rule::unique('store_products', 'sku')->ignore($product?->id)],
            'store_category_id' => ['nullable', 'integer', Rule::exists('store_category_translations', 'store_category_id')->where('lang_id', $languageId)],
            'source_product_id' => ['nullable', 'integer', 'min:1'],
            'short_description' => ['nullable', 'string', 'max:1000'],
            'content' => ['nullable', 'string', 'max:65535'],
            'price' => ['required', ...$amount], 'compare_at_price' => $amount,
            'currency' => ['required', Rule::in(array_keys(StoreMoney::Currencies))],
            'tracks_stock' => ['required', 'boolean'], 'allow_backorders' => ['required', 'boolean'],
            'stock_quantity' => ['required', 'integer', 'min:-1000000', 'max:1000000'],
            'stock_reason' => ['nullable', 'string', 'max:255'],
            'low_stock_threshold' => ['required', 'integer', 'min:0', 'max:1000000'],
            'is_active' => ['required', 'boolean'], 'has_variants' => ['required', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'option_groups' => ['nullable', 'array', 'max:3'],
            'option_groups.*.name' => ['required', 'string', 'max:50', 'distinct', 'not_regex:/[\[\]]/'],
            'option_groups.*.values' => ['required', 'string', 'max:500'],
            'variants' => ['nullable', 'array', 'max:100'],
            'variants.*.id' => ['nullable', 'integer', 'distinct'],
            'variants.*.sku' => ['required', 'string', 'max:100', 'distinct'],
            'variants.*.option_values' => ['required', 'array', 'min:1', 'max:3'],
            'variants.*.option_values.*' => ['required', 'string', 'max:100'],
            'variants.*.price' => $amount,
            'variants.*.stock_quantity' => ['required', 'integer', 'min:-1000000', 'max:1000000'],
            'variants.*.is_active' => ['required', 'boolean'],
            'variants.*.image_media_id' => ['nullable', ...array_filter(app(MediaSelection::class)->imageRules(), fn ($rule): bool => ! in_array($rule, ['required', 'distinct'], true))],
            'image_media_ids' => ['present', 'list', 'max:100'],
            'image_media_ids.*' => app(MediaSelection::class)->imageRules(),
            ...$media->rules($media->definition()),
            ...$media->rules($media->definition('share_image', 'Paylaşım görseli')),
            'meta_title' => ['nullable', 'string', 'max:255'],
            'meta_description' => ['nullable', 'string', 'max:500'],
            'weight_grams' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'width_cm' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'height_cm' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'length_cm' => ['nullable', 'integer', 'min:0', 'max:10000'],
            'preparation_days' => ['required', 'integer', 'min:0', 'max:365'],
            'delivery_description' => ['nullable', 'string', 'max:2000'],
            ...$values->rules($features->form('Store', 'product')['fields'], $this->all(), $product?->translations()->where('lang_id', $languageId)->value('extra_fields') ?? []),
        ];
    }

    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('product') === null ? 'store.create' : 'store.update') ?? false;
    }
}
