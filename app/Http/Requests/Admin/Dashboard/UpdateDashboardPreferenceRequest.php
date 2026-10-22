<?php

namespace App\Http\Requests\Admin\Dashboard;

use Illuminate\Foundation\Http\FormRequest;

class UpdateDashboardPreferenceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    protected function prepareForValidation(): void
    {
        abort_if(strlen($this->getContent()) > 32768, 413, 'Widget isteği çok büyük.');
        abort_if(array_diff(array_keys($this->all()), ['widget_order', 'revision']) !== [], 422, 'Beklenmeyen istek alanı.');
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'widget_order' => ['present', 'array', 'list', 'max:200'],
            'widget_order.*' => ['required', 'string', 'max:120', 'distinct:strict', 'regex:/^[a-z0-9]+(?:[.][a-z0-9_-]+)+$/'],
            'revision' => ['required', 'integer', 'min:0', 'max:2147483646'],
        ];
    }
}
