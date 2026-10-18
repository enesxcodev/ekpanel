<?php

namespace App\Http\Requests\Admin\Auth;

use Illuminate\Foundation\Http\FormRequest;

class AdminLoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, array<int, string>> */
    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'],
            'password' => ['required', 'string'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'email.required' => 'E-posta adresinizi girin.',
            'email.string' => 'Geçerli bir e-posta adresi girin.',
            'email.email' => 'Geçerli bir e-posta adresi girin.',
            'email.max' => 'E-posta adresi en fazla 255 karakter olabilir.',
            'password.required' => 'Şifrenizi girin.',
            'password.string' => 'Geçerli bir şifre girin.',
        ];
    }
}
