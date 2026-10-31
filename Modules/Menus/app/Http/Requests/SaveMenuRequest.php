<?php

namespace Modules\Menus\Http\Requests;

use App\Models\Settings\Language;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

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
            'lang_id' => ['required', 'integer', Rule::exists('languages', 'id')->where('is_active', true)],
            'icon' => ['nullable', 'string', 'max:100', 'regex:/^[a-z][a-z0-9-]*$/'],
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_-]*$/', Rule::unique('menus', 'key')->ignore($this->route('menu')?->id)],
            'is_active' => ['required', 'boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (! $this->exists('lang_id')) {
            $this->merge(['lang_id' => Language::query()->where('code', 'tr')->where('is_active', true)->value('id')]);
        }
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            $code = $this->query('lang');
            if ($validator->errors()->isNotEmpty() || ! is_string($code) || $code === '') {
                return;
            }

            $language = Language::query()->where('code', $code)->where('is_active', true)->first();
            if ($language === null || (int) $this->input('lang_id') !== $language->id) {
                $validator->errors()->add('lang_id', 'Form dili ile istek dili eşleşmiyor. Sayfayı yenileyip tekrar deneyin.');
            }
        }];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['name.required' => 'Menü adını girin.', 'key.required' => 'Menü anahtarını girin.', 'key.regex' => 'Anahtar küçük harfle başlamalı; yalnız küçük harf, rakam, tire ve alt çizgi içermelidir.', 'key.unique' => 'Bu menü anahtarı kullanılıyor.'];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return ['icon' => 'İkon', 'name' => 'Menü adı', 'key' => 'Menü anahtarı', 'is_active' => 'Durum'];
    }
}
