<?php

namespace Modules\Products\Http\Requests;

class UpdateProductCategoryRequest extends StoreProductCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('products.categories.update') ?? false;
    }
}
