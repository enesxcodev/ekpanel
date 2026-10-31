<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveLanguageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return ($this->user('admin')?->can('settings.view') ?? false)
            && ($this->user('admin')?->can('settings.update') ?? false);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return $this->isMethod('post')
            ? ['code' => ['bail', 'required', 'string', Rule::in(array_keys(config('languages.catalog'))), Rule::unique('languages', 'code')]]
            : ['is_active' => ['required', 'boolean']];
    }

    public function messages(): array
    {
        return [
            'code.required' => 'Bir dil seçin.',
            'code.string' => 'Geçerli bir dil seçin.',
            'code.in' => 'Geçerli bir dil seçin.',
            'code.unique' => 'Bu dil zaten eklenmiş. Listeden durumunu değiştirebilirsiniz.',
            'is_active.required' => 'Dil durumunu belirtin.',
            'is_active.boolean' => 'Geçerli bir dil durumu seçin.',
        ];
    }
}
