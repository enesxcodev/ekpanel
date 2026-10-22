<?php

namespace Modules\Store\Services;

use App\Models\Admin\Admin;
use App\Models\Media\MediaUsage;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\HtmlSanitizer;
use App\Services\Media\ContentMediaService;
use App\Services\Media\MediaSelection;
use App\Services\Media\MediaService;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Cache;
use Illuminate\Validation\ValidationException;
use Modules\Store\Models\StockMovement;
use Modules\Store\Models\StoreProduct;
use Modules\Store\Models\StoreVariant;
use Nwidart\Modules\Facades\Module;

class StoreProductService
{
    public function __construct(private HtmlSanitizer $sanitizer, private ContentMediaService $contentMedia, private MediaService $media, private MediaSelection $selection, private FieldFiles $files, private ModuleFeatures $features, private FeatureValues $values) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?StoreProduct $product, Admin $actor): StoreProduct
    {
        return Cache::lock('store.catalog-write', 120)->block(10, fn (): StoreProduct => $this->files->transaction(function (FieldFiles $files) use ($data, $product, $actor): StoreProduct {
            $product = $product ? StoreProduct::query()->lockForUpdate()->findOrFail($product->id) : new StoreProduct;
            $oldStock = (int) ($product->stock_quantity ?? 0);
            if ($product->exists && ! $actor->can('store.stock.update') && ($oldStock !== (int) $data['stock_quantity'] || (bool) $product->has_variants !== (bool) $data['has_variants'])) {
                abort(403);
            }
            if (! $data['allow_backorders'] && (int) $data['stock_quantity'] < 0) {
                throw ValidationException::withMessages(['stock_quantity' => 'Stok negatif olamaz.']);
            }
            $price = StoreMoney::parse($data['price'], $data['currency']);
            $compare = filled($data['compare_at_price'] ?? null) ? StoreMoney::parse($data['compare_at_price'], $data['currency']) : null;
            if ($compare !== null && $compare <= $price) {
                throw ValidationException::withMessages(['compare_at_price' => 'Karşılaştırma fiyatı satış fiyatından büyük olmalı.']);
            }
            if (StoreVariant::query()->where('sku', $data['sku'])->exists()) {
                throw ValidationException::withMessages(['sku' => 'Bu SKU bir varyantta kullanılıyor.']);
            }
            $source = $product->source_product_id;
            if (! $product->exists && ! empty($data['source_product_id'])) {
                abort_unless((Module::find('Products')?->isEnabled() ?? false) && $actor->can('products.view') && $actor->can('media.view'), 403);
                $class = 'Modules\\Products\\Models\\Product';
                abort_unless(class_exists($class), 404);
                $sourceProduct = $class::query()->findOrFail($data['source_product_id']);
                if (! $sourceProduct->image_media_id && empty($data['image_media_id'])) {
                    $product->image = $sourceProduct->image;
                }
                if (StoreProduct::query()->where('source_product_id', $data['source_product_id'])->exists()) {
                    throw ValidationException::withMessages(['source_product_id' => 'Bu katalog ürünü zaten aktarılmış. Mevcut Store ürününü düzenleyin.']);
                }
                $source = $data['source_product_id'];
            }
            $product->fill([
                ...Arr::only($data, ['title', 'slug', 'sku', 'store_category_id', 'short_description', 'currency', 'tracks_stock', 'stock_quantity', 'low_stock_threshold', 'allow_backorders', 'is_active', 'sort_order', 'has_variants', 'option_groups', 'meta_title', 'meta_description', 'weight_grams', 'width_cm', 'height_cm', 'length_cm', 'preparation_days', 'delivery_description']),
                'source_product_id' => $source, 'price_minor' => $price, 'compare_at_price_minor' => $compare,
                'content' => isset($data['content']) ? $this->sanitizer->sanitize($data['content']) : null,
                ...$this->contentMedia->prepare($product, $data, $this->contentMedia->definition(), $actor),
                ...$this->contentMedia->prepare($product, $data, $this->contentMedia->definition('share_image'), $actor),
                'extra_fields' => $this->values->prepare($this->features->form('Store', 'product')['fields'], $data, $product->extra_fields ?? [], 'store/features', $files, $actor),
            ])->save();
            if ((int) $product->stock_quantity !== $oldStock) {
                $this->movement($product, null, $oldStock, (int) $product->stock_quantity, $data['stock_reason'] ?? null, $actor);
            }
            $ids = array_map(intval(...), $data['image_media_ids'] ?? []);
            $images = $this->selection->images($ids, $actor, $product->images()->pluck('media_files.id')->all());
            $product->images()->sync(collect($ids)->mapWithKeys(fn (int $id, int $index): array => [$id => ['sort_order' => $index]])->all());
            MediaUsage::query()->where('owner_type', $product->getMorphClass())->where('owner_id', $product->id)->where('field', 'like', 'gallery.%')->delete();
            foreach ($images as $image) {
                $this->media->attachUsage($image, $product, 'gallery.'.$image->id, 'Mağaza / '.$product->title, 'admin.store.edit');
            }
            foreach (['image', 'share_image'] as $field) {
                $this->contentMedia->syncUsage($product, $field, 'Mağaza / '.$product->title, 'admin.store.edit');
            }
            $this->values->syncMediaUsages($this->features->form('Store', 'product')['fields'], $product, $this->media, 'Mağaza / '.$product->title, 'admin.store.edit');
            $kept = [];
            $combinations = [];
            $active = 0;
            MediaUsage::query()->where('owner_type', $product->getMorphClass())->where('owner_id', $product->id)->where('field', 'like', 'variant.%')->delete();
            foreach ($data['variants'] ?? [] as $index => $row) {
                $variant = empty($row['id']) ? new StoreVariant(['store_product_id' => $product->id]) : $product->variants()->lockForUpdate()->findOrFail($row['id']);
                $old = (int) ($variant->stock_quantity ?? 0);
                if ($product->exists && ! $product->wasRecentlyCreated && (! $variant->exists || $old !== (int) $row['stock_quantity'])) {
                    abort_unless($actor->can('store.stock.update'), 403);
                }
                $options = $row['option_values'];
                ksort($options);
                $key = json_encode($options, JSON_THROW_ON_ERROR);
                if (isset($combinations[$key]) || StoreProduct::query()->where('sku', $row['sku'])->exists() || StoreVariant::query()->where('sku', $row['sku'])->when($variant->exists, fn ($query) => $query->whereKeyNot($variant->id))->exists()) {
                    throw ValidationException::withMessages(['variants' => 'Varyant kombinasyonu veya SKU tekrarlanamaz.']);
                }
                foreach ($options as $name => $value) {
                    $group = collect($data['option_groups'] ?? [])->firstWhere('name', $name);
                    if (! $group || ! in_array($value, array_map('trim', explode(',', $group['values'])), true)) {
                        throw ValidationException::withMessages(['variants' => 'Varyant seçenekleri gruplarla eşleşmeli.']);
                    }
                }
                if (count($options) !== count($data['option_groups'] ?? []) || (! $product->allow_backorders && (int) $row['stock_quantity'] < 0)) {
                    throw ValidationException::withMessages(['variants' => 'Varyant seçenekleri veya stok geçersiz.']);
                }
                $combinations[$key] = true;
                $variant->fill(['title' => implode(' / ', $options), 'sku' => $row['sku'], 'option_values' => $options, 'price_minor' => filled($row['price'] ?? null) ? StoreMoney::parse($row['price'], $product->currency) : null, 'stock_quantity' => $row['stock_quantity'], 'is_active' => $row['is_active'], 'sort_order' => $index, 'is_archived' => false]);
                $selected = $this->selection->images(empty($row['image_media_id']) ? [] : [(int) $row['image_media_id']], $actor, $variant->image_media_id ? [$variant->image_media_id] : []);
                $variant->image_media_id = $selected->first()?->id;
                $variant->save();
                $this->media->detachUsages($variant);
                if ($image = $selected->first()) {
                    $this->media->attachUsage($image, $product, 'variant.'.$variant->id, 'Varyant / '.$variant->title, 'admin.store.edit');
                }
                if ($old !== (int) $variant->stock_quantity) {
                    $this->movement($product, $variant, $old, (int) $variant->stock_quantity, $data['stock_reason'] ?? null, $actor);
                }
                $kept[] = $variant->id;
                $active += (int) $variant->is_active;
            }
            $product->variants()->whereNotIn('id', $kept)->update(['is_archived' => true, 'is_active' => false]);
            if ($product->is_active && $product->has_variants && $active === 0) {
                throw ValidationException::withMessages(['variants' => 'Yayınlamak için en az bir aktif varyant ekleyin.']);
            }

            return $product;
        }));
    }

    private function movement(StoreProduct $product, ?StoreVariant $variant, int $old, int $new, ?string $reason, Admin $actor): void
    {
        if ($product->wasRecentlyCreated || $variant?->wasRecentlyCreated) {
            $reason = $reason ?: 'İlk stok';
        }
        if (! filled($reason)) {
            throw ValidationException::withMessages(['stock_reason' => 'Stok değişikliği için gerekçe girin.']);
        }
        StockMovement::query()->create(['store_product_id' => $product->id, 'store_variant_id' => $variant?->id, 'quantity' => $new - $old, 'resulting_quantity' => $new, 'reason' => $reason, 'admin_id' => $actor->id]);
    }

    public function delete(StoreProduct $product): void
    {
        $product->update(['is_active' => false, 'is_archived' => true]);
    }
}
