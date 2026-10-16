<?php

namespace App\Http\Requests\Admin\Settings;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveSettingSectionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can('manage-setting-definitions') ?? false;
    }

    public function rules(): array
    {
        $section = $this->route('section');

        return [
            'key' => ['required', 'string', 'max:80', 'regex:/^[a-z][a-z0-9_]*$/', Rule::unique('setting_sections', 'key')->ignore($section?->id), ...($section ? [Rule::in([$section->key])] : [])],
            'label' => ['required', 'string', 'max:150'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:100000'],
        ];
    }

    public function messages(): array
    {
        return [
            'key.required' => 'Bölüm anahtarını girin.', 'key.regex' => 'Anahtar küçük harfle başlamalı; yalnızca küçük harf, sayı ve alt çizgi içermelidir.',
            'key.unique' => 'Bu bölüm anahtarı zaten kullanılıyor.', 'key.in' => 'Bölüm anahtarı oluşturulduktan sonra değiştirilemez.',
            'label.required' => 'Bölüm adını girin.', 'label.max' => 'Bölüm adı en fazla 150 karakter olabilir.',
            'sort_order.required' => 'Sıralama değerini girin.', 'sort_order.integer' => 'Sıralama tam sayı olmalıdır.',
        ];
    }
}
