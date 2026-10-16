<?php

namespace App\Services\Fields;

use App\Enums\Fields\FieldInputType;
use App\Enums\Fields\FieldValueType;
use Closure;
use Illuminate\Validation\Rule;

class FieldRuleBuilder
{
    /** @return list<mixed> */
    public function forDefinition(FieldDefinition $definition, bool $required): array
    {
        $rules = ['bail', $required ? 'required' : 'nullable'];
        $validation = $definition->validation ?? [];
        $type = $definition->input_type;

        if ($type->isFile()) {
            $extensions = $validation['extensions'] ?? ($type === FieldInputType::Image ? ['jpg', 'jpeg', 'png', 'webp'] : ['pdf']);
            $rules = [...$rules, 'file', 'mimes:'.implode(',', $extensions), 'extensions:'.implode(',', $extensions), 'max:'.($validation['max_size'] ?? 2048)];
            if ($type === FieldInputType::Image) {
                $rules[] = 'image';
                $dimensions = array_intersect_key($validation, array_flip(['min_width', 'min_height', 'max_width', 'max_height']));
                if ($dimensions !== []) {
                    $rules[] = Rule::dimensions($dimensions);
                }
            }

            return $rules;
        }

        $rules[] = match ($definition->value_type) {
            FieldValueType::Integer => 'integer', FieldValueType::Float => 'numeric',
            FieldValueType::Boolean => 'boolean', default => 'string',
        };

        if ($type === FieldInputType::Email) {
            $rules[] = 'email';
        }
        if ($type->hasOptions()) {
            $rules[] = Rule::in(array_column($definition->options ?? [], 'value'));
        }
        foreach (['min', 'max'] as $bound) {
            if (isset($validation[$bound])) {
                $rules[] = $bound.':'.$validation[$bound];
            }
        }
        if ($definition->value_type === FieldValueType::String && ! isset($validation['max'])) {
            $rules[] = 'max:10000';
        }
        if (in_array($type, [FieldInputType::Number, FieldInputType::Range], true) && isset($definition->attributes['step'])) {
            $step = (float) $definition->attributes['step'];
            $base = (float) ($validation['min'] ?? 0);
            $rules[] = function (string $attribute, mixed $value, Closure $fail) use ($step, $base): void {
                $steps = ((float) $value - $base) / $step;
                if (abs($steps - round($steps)) > 0.000001) {
                    $fail('Değer, minimum değerden itibaren '.$step.' adımına uygun olmalıdır.');
                }
            };
        }

        return $rules;
    }

    /** @return array<string, string|array<string, string>> */
    public function messages(): array
    {
        return [
            'required' => ':attribute alanını doldurun.', 'present' => ':attribute alanını gönderin.',
            'string' => ':attribute metin olmalıdır.', 'integer' => ':attribute tam sayı olmalıdır.',
            'numeric' => ':attribute sayı olmalıdır.', 'boolean' => ':attribute geçerli bir açık / kapalı değeri olmalıdır.',
            'email' => ':attribute geçerli bir e-posta adresi olmalıdır.', 'in' => ':attribute için tanımlı bir seçenek seçin.',
            'array' => ':attribute içerisinde yalnızca bu bölüme ait izin verilen alanları gönderin.',
            'min' => ['string' => ':attribute en az :min karakter olmalıdır.', 'numeric' => ':attribute en az :min olmalıdır.'],
            'max' => ['string' => ':attribute en fazla :max karakter olabilir.', 'numeric' => ':attribute en fazla :max olabilir.', 'file' => ':attribute en fazla :max KB olabilir.'],
            'image' => ':attribute geçerli bir görsel olmalıdır.',
            'file' => ':attribute geçerli bir dosya olmalıdır.', 'mimes' => ':attribute için izin verilen dosya türleri: :values.',
            'extensions' => ':attribute dosya uzantısı izin verilen türlerden olmalıdır.',
            'dimensions' => ':attribute görsel ölçüleri tanımlanan sınırlara uymalıdır.',
            'multiple_of' => ':attribute değeri :value adımına uygun olmalıdır.',
        ];
    }
}
