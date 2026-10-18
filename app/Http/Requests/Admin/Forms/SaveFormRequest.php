<?php

namespace App\Http\Requests\Admin\Forms;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveFormRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->can($this->route('form') ? 'forms.update' : 'forms.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        $fields = $this->input('fields');
        if (is_string($fields)) {
            $decoded = json_decode($fields, true);
            if (is_array($decoded)) {
                $this->merge(['fields' => $decoded]);
            }
        }
    }

    public function rules(): array
    {
        $classes = 'regex:/^[a-zA-Z0-9_ :\\-]*$/';

        return [
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_-]*$/', Rule::unique('site_forms', 'key')->ignore($this->route('form')?->id)],
            'is_active' => ['required', 'boolean'], 'submit_label' => ['required', 'string', 'max:100'],
            'success_message' => ['required', 'string', 'max:500'], 'notification_email' => ['nullable', 'email', 'max:255'],
            'css_class' => ['nullable', 'string', 'max:255', $classes],
            'fields' => ['present', 'list', 'max:50'],
            'fields.*' => ['array:key,label,input_type,required,placeholder,description,width,css_class,input_class,role,options,max_length,max_size,extensions'],
            'fields.*.key' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_]*$/', 'distinct'],
            'fields.*.label' => ['required', 'string', 'max:150'],
            'fields.*.input_type' => ['required', Rule::in(['text', 'email', 'tel', 'textarea', 'number', 'select', 'radio', 'boolean', 'file'])],
            'fields.*.required' => ['required', 'boolean'],
            'fields.*.placeholder' => ['nullable', 'string', 'max:255'], 'fields.*.description' => ['nullable', 'string', 'max:500'],
            'fields.*.width' => ['required', Rule::in(['full', 'half'])],
            'fields.*.css_class' => ['nullable', 'string', 'max:255', $classes], 'fields.*.input_class' => ['nullable', 'string', 'max:255', $classes],
            'fields.*.role' => ['nullable', Rule::in(['name', 'email', 'phone'])],
            'fields.*.options' => ['present', 'list', 'max:100'],
            'fields.*.options.*' => ['array:label,value'],
            'fields.*.options.*.label' => ['required', 'string', 'max:150'],
            'fields.*.options.*.value' => ['required', 'string', 'max:150', 'regex:/^[a-zA-Z0-9_-]+$/'],
            'fields.*.max_length' => ['nullable', 'integer', 'between:1,10000'],
            'fields.*.max_size' => ['nullable', 'integer', 'between:1,10240'],
            'fields.*.extensions' => ['nullable', 'string', 'max:150', 'regex:/^(?:pdf|doc|docx|txt|jpg|jpeg|png|webp)(?:,(?:pdf|doc|docx|txt|jpg|jpeg|png|webp))*$/'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $roles = [];
            foreach ($this->input('fields', []) as $index => $field) {
                $optionValues = array_column($field['options'], 'value');
                if (count($optionValues) !== count(array_unique($optionValues))) {
                    $validator->errors()->add('fields', 'Aynı alandaki seçenek değerleri benzersiz olmalıdır.');
                }
                if (in_array($field['input_type'], ['select', 'radio'], true) && empty($field['options'])) {
                    $validator->errors()->add('fields', 'Seçim alanlarına en az bir seçenek ekleyin.');
                }
                $role = $field['role'] ?? null;
                if ($role && (in_array($role, $roles, true) || in_array($field['input_type'], ['file', 'boolean', 'select', 'radio'], true) || ($role === 'email' && $field['input_type'] !== 'email'))) {
                    $validator->errors()->add('fields', 'İletişim eşleşmelerini benzersiz ve uygun alan türleriyle tanımlayın.');
                }
                if ($role) {
                    $roles[] = $role;
                }
            }
            if ($this->boolean('is_active') && $this->input('fields') === []) {
                $validator->errors()->add('fields', 'Aktif formda en az bir alan olmalıdır.');
            }
        }];
    }
}
