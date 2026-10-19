<?php

namespace App\Http\Requests\Admin\Notifications;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class UpdateNotificationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin') !== null;
    }

    /** @return array<string, array<int, mixed>> */
    public function rules(): array
    {
        return [
            'action' => ['required', Rule::in(['read', 'archive', 'restore'])],
            'scope' => ['required', Rule::in(['selected', 'all'])],
            'ids' => ['required_if:scope,selected', 'prohibited_if:scope,all', 'array', 'min:1', 'max:100'],
            'ids.*' => ['required', 'uuid', 'distinct'],
        ];
    }

    /** @return array<int, callable(Validator): void> */
    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($this->input('scope') === 'all' && $this->input('action') === 'restore') {
                $validator->errors()->add('action', 'Arşivden çıkarma işlemi için bildirim seçin.');
            }
        }];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'ids.required_if' => 'Bu sayfadan en az bir bildirim seçin.',
            'ids.max' => 'Bir işlemde en fazla 100 bildirim seçebilirsiniz.',
        ];
    }
}
