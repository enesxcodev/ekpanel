<?php

namespace Modules\Store\Services;

use App\Models\Admin\Admin;
use App\Services\HtmlSanitizer;
use Modules\Store\Models\StoreProduct;

class StoreProductService
{
    public function __construct(private HtmlSanitizer $sanitizer) {}

    /** @param array<string, mixed> $data */
    public function save(array $data, ?StoreProduct $product, Admin $actor): StoreProduct
    {
        $product ??= new StoreProduct;
        $product->fill([
            ...$data,
            'content' => isset($data['content']) ? $this->sanitizer->sanitize($data['content']) : null,
            'currency' => strtoupper($data['currency']),
        ])->save();

        return $product;
    }
}
