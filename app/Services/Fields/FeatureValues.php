<?php

namespace App\Services\Fields;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class FeatureValues
{
    /**
     * @param  array<string, FieldDefinition>  $definitions
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $existing
     * @return array<string, list<mixed>>
     */
    public function rules(array $definitions, array $payload = [], array $existing = []): array
    {
        $keys = array_keys($definitions);
        $fileKeys = array_keys(array_filter($definitions, fn (FieldDefinition $field): bool => $field->input_type->isFile()));
        $rules = [
            'extra_fields' => ['sometimes', $keys === [] ? 'array' : 'array:'.implode(',', $keys), ...($keys === [] ? ['size:0'] : [])],
            'remove_features' => ['sometimes', $fileKeys === [] ? 'array' : 'array:'.implode(',', $fileKeys), ...($fileKeys === [] ? ['size:0'] : [])],
        ];
        foreach ($definitions as $key => $definition) {
            $required = (bool) ($definition->validation['required'] ?? false);
            $isFile = $definition->input_type->isFile();
            $removing = filter_var($payload['remove_features'][$key] ?? false, FILTER_VALIDATE_BOOL);
            $canKeep = $isFile && ! empty($existing[$key]) && ! $removing;
            $rules['extra_fields.'.$key] = (new FieldRuleBuilder)->forDefinition($definition, $required && ! $canKeep);
            if ($isFile) {
                $rules['remove_features.'.$key] = ['sometimes', 'boolean'];
            }
        }

        return $rules;
    }

    /** @param array<string, FieldDefinition> $definitions @return array<string, string> */
    public function labels(array $definitions): array
    {
        $labels = ['extra_fields' => 'Ek alanlar', 'remove_features' => 'Kaldırılacak dosyalar'];
        foreach ($definitions as $key => $definition) {
            $labels['extra_fields.'.$key] = $definition->label;
        }

        return $labels;
    }

    /**
     * @param  array<string, FieldDefinition>  $definitions
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $existing
     * @return array<string, mixed>
     */
    public function prepare(array $definitions, array $payload, array $existing, string $directory, FieldFiles $files): array
    {
        $values = $existing;
        foreach ($definitions as $key => $definition) {
            $input = $payload['extra_fields'][$key] ?? null;
            $removing = filter_var($payload['remove_features'][$key] ?? false, FILTER_VALIDATE_BOOL);
            if ($definition->input_type->isFile()) {
                if ($removing && $input instanceof UploadedFile) {
                    throw ValidationException::withMessages(['extra_fields.'.$key => 'Yeni dosya yükleme ve mevcut dosyayı kaldırma işlemlerinden birini seçin.']);
                }
                $values[$key] = $files->replace($input, $existing[$key] ?? null, $directory.'/'.$key, $removing);
            } elseif (array_key_exists($key, $payload['extra_fields'] ?? [])) {
                $values[$key] = $definition->value_type->normalize($input);
            } elseif (! array_key_exists($key, $values)) {
                $values[$key] = $definition->value_type->normalize($definition->default_value);
            }
        }

        return $values;
    }

    /**
     * @param  array<string, FieldDefinition>  $definitions
     * @param  array<string, mixed>  $values
     * @return array<string, array{value: mixed, hasValue: bool, fileUrl: ?string}>
     */
    public function fields(array $definitions, array $values): array
    {
        $fields = [];
        foreach ($definitions as $key => $definition) {
            $value = array_key_exists($key, $values) ? $values[$key] : $definition->value_type->normalize($definition->default_value);
            $fields[$key] = ['value' => $value, 'hasValue' => $value !== null && $value !== '', 'fileUrl' => $definition->input_type->isFile() && $value ? Storage::disk('public')->url($value) : null];
        }

        return $fields;
    }
}
