<?php

namespace Modules\Services\Http\Requests;

class UpdateServiceRequest extends StoreServiceRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('services.update') ?? false;
    }
}
