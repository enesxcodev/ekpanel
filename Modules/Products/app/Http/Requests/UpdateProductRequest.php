<?php

namespace Modules\Products\Http\Requests;

class UpdateProductRequest extends StoreProductRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('products.update') ?? false;
    }
}
