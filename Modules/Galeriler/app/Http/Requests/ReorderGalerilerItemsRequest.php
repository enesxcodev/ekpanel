<?php

namespace Modules\Galeriler\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReorderGalerilerItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('galeriler.update') ?? false;
    }

    public function rules(): array
    {
        return ['category_id' => ['required', 'integer', 'exists:galeriler_categories,id'], 'ids' => ['required', 'list', 'min:1', 'max:100'], 'ids.*' => ['required', 'integer', 'distinct'], 'search' => ['prohibited'], 'type' => ['prohibited']];
    }
}
