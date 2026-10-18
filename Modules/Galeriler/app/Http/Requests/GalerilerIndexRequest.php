<?php

namespace Modules\Galeriler\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GalerilerIndexRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('galeriler.view') ?? false;
    }

    public function rules(): array
    {
        return ['category_id' => ['nullable', 'integer', 'exists:galeriler_categories,id'], 'type' => ['nullable', Rule::in(['photo', 'video'])], 'search' => ['nullable', 'string', 'max:255']];
    }
}
