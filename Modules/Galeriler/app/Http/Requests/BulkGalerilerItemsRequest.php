<?php

namespace Modules\Galeriler\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BulkGalerilerItemsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->input('action') === 'delete' ? 'galeriler.delete' : 'galeriler.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['delete', 'move'])],
            'ids' => ['required', 'list', 'min:1', 'max:100'],
            'ids.*' => ['required', 'integer', 'distinct', 'exists:galeriler_items,id'],
            'galeriler_category_id' => ['required_if:action,move', 'nullable', 'integer', 'exists:galeriler_categories,id'],
        ];
    }
}
