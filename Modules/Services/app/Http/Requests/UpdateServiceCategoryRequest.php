<?php

namespace Modules\Services\Http\Requests;

class UpdateServiceCategoryRequest extends StoreServiceCategoryRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('services.categories.update') ?? false;
    }
}
