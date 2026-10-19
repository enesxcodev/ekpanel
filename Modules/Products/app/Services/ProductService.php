<?php

namespace Modules\Products\Services;

use App\Enums\Fields\FieldValueType;
use App\Models\Admin\Admin;
use App\Models\Media\MediaUsage;
use App\Services\Fields\FeatureValues;
use App\Services\Fields\FieldFiles;
use App\Services\Fields\ModuleFeatures;
use App\Services\HtmlSanitizer;
use App\Services\Media\ContentMediaService;
use App\Services\Media\MediaSelection;
use App\Services\Media\MediaService;
use Modules\Products\Models\Product;

class ProductService
{
    public function __construct(private ModuleFeatures $features, private FeatureValues $values, private FieldFiles $files, private HtmlSanitizer $sanitizer, private MediaService $media, private ContentMediaService $contentMedia, private MediaSelection $selection) {}

    /** @param array<string, mixed> $data */
    public function create(array $data, Admin $actor): Product
    {
        return $this->save($data, null, $actor);
    }

    /** @param array<string, mixed> $data */
    public function save(array $data, ?Product $product, Admin $actor): Product
    {
        return $this->files->transaction(function (FieldFiles $files) use ($data, $product, $actor): Product {
            $product = $product === null ? new Product : Product::query()->lockForUpdate()->findOrFail($product->id);
            $product->fill([
                'product_category_id' => $data['product_category_id'] ?? null,
                'title' => $data['title'],
                'slug' => $data['slug'] ?? null,
                'sort_order' => $data['sort_order'] ?? 0,
                'short_description' => $data['short_description'] ?? null,
                'content' => isset($data['content']) ? $this->sanitizer->sanitize($data['content']) : null,
                'is_active' => (bool) ($data['is_active'] ?? true),
                ...$this->contentMedia->prepare($product, $data, $this->contentMedia->definition(), $actor),
                'site_form_id' => $data['site_form_id'] ?? null,
                'extra_fields' => $this->values->prepare($this->features->form('Products', 'product')['fields'], $data, $product->extra_fields ?? [], 'products/features', $files, $actor),
            ])->save();

            $ids = array_map(intval(...), $data['image_media_ids'] ?? []);
            $images = $this->selection->images($ids, $actor, $product->images()->pluck('media_files.id')->all());
            $pivot = [];
            foreach ($ids as $index => $id) {
                $pivot[$id] = ['sort_order' => $index];
            }
            $product->images()->sync($pivot);
            MediaUsage::query()->where('owner_type', $product->getMorphClass())->where('owner_id', $product->id)->where('field', 'like', 'product_image.%')->delete();
            foreach ($images as $image) {
                $this->media->attachUsage($image, $product, 'product_image.'.$image->id, 'Ürün / '.$product->title, 'admin.products.edit');
            }
            $this->contentMedia->syncUsage($product, 'image', 'Ürün / '.$product->title, 'admin.products.edit');
            $this->values->syncMediaUsages($this->features->form('Products', 'product')['fields'], $product, $this->media, 'Ürün / '.$product->title, 'admin.products.edit');

            return $product;
        });
    }

    public function delete(Product $product): void
    {
        $this->files->transaction(function (FieldFiles $files) use ($product): void {
            $product = Product::query()->lockForUpdate()->findOrFail($product->id);
            foreach ($this->features->form('Products', 'product')['fields'] as $key => $definition) {
                if ($definition->input_type->isFile() && $definition->value_type !== FieldValueType::Media) {
                    $files->replace(null, $product->extra_fields[$key] ?? null, 'products/features', true);
                }
            }
            $this->media->detachUsages($product);
            $product->delete();
        });
    }
}
