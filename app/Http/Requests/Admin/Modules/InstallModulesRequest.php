<?php

namespace App\Http\Requests\Admin\Modules;

use App\Enums\AdminType\AdminTypeEnum;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class InstallModulesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user('admin')?->status
            && $this->user('admin')?->type === AdminTypeEnum::SuperAdmin;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'modules' => ['required', 'list', 'min:1', 'max:20'],
            'modules.*' => ['required', 'string', 'distinct', 'regex:/^[a-z][a-z0-9-]*$/D', 'max:80'],
        ];
    }
}
