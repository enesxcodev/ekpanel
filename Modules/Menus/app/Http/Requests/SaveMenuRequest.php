<?php

namespace Modules\Menus\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('menu') ? 'menus.update' : 'menus.create') ?? false;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_-]*$/', Rule::unique('menus', 'key')->ignore($this->route('menu')?->id)],
            'is_active' => ['required', 'boolean'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['name.required' => 'Menü adını girin.', 'key.required' => 'Menü anahtarını girin.', 'key.regex' => 'Anahtar küçük harfle başlamalı; yalnız küçük harf, rakam, tire ve alt çizgi içermelidir.', 'key.unique' => 'Bu menü anahtarı kullanılıyor.'];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['name' => 'Menü adı', 'key' => 'Menü anahtarı', 'is_active' => 'Durum'];
    }
}
