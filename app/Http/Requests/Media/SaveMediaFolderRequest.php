<?php

namespace App\Http\Requests\Media;

use Illuminate\Foundation\Http\FormRequest;

class SaveMediaFolderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->isMethod('POST') ? 'media.create' : 'media.update') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150', 'not_regex:~[\\\\/<>\x00-\x1f]~', 'not_in:.,..'],
            'parent_id' => ['nullable', 'integer', 'exists:media_folders,id'],
        ];
    }

    public function messages(): array
    {
        return ['name.required' => 'Klasör adını girin.', 'name.not_regex' => 'Klasör adında geçersiz karakter var.', 'name.max' => 'Klasör adı en fazla 150 karakter olabilir.'];
    }
}
