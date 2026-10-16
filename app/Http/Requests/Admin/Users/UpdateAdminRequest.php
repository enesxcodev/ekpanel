<?php

namespace App\Http\Requests\Admin\Users;

use App\Enums\AdminType\AdminTypeEnum;
use App\Models\Admin\Admin;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UpdateAdminRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['email' => Str::lower(trim((string) $this->input('email')))]);
    }

    public function authorize(): bool
    {
        $admin = $this->route('admin');

        return $admin instanceof Admin && ($this->user('admin')?->can('update', $admin) ?? false);
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        $admin = $this->route('admin');

        return [
            'name' => ['required', 'string', 'max:255'],
            'lastname' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('admins', 'email')->ignore($admin)],
            'password' => ['nullable', 'string', 'confirmed', Password::min(12)],
            'type' => ['required', Rule::enum(AdminTypeEnum::class), Rule::in($this->user('admin')->type === AdminTypeEnum::SuperAdmin ? [0, 1] : [0])],
            'status' => ['required', 'boolean'],
        ];
    }
}
