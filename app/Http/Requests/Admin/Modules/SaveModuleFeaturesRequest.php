<?php

namespace App\Http\Requests\Admin\Modules;

use App\Enums\AdminType\AdminTypeEnum;
use App\Services\Fields\ModuleFeatures;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use RuntimeException;

class SaveModuleFeaturesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user('admin')?->status
            && $this->user('admin')?->type === AdminTypeEnum::SuperAdmin;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('document'))) {
            $document = json_decode($this->input('document'), true);
            if (is_array($document)) {
                $this->merge(['document' => $document]);
            }
        }
    }

    public function rules(): array
    {
        return ['document' => ['required', 'array'], 'revision' => ['required', 'string', 'size:64']];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            try {
                app(ModuleFeatures::class)->validateDocument($this->input('document'));
            } catch (RuntimeException $exception) {
                $validator->errors()->add('document', $exception->getMessage());
            }
        }];
    }
}
