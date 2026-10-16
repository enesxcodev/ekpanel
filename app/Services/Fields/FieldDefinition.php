<?php

namespace App\Services\Fields;

use App\Enums\Fields\FieldInputType;
use App\Enums\Fields\FieldValueType;

readonly class FieldDefinition
{
    /**
     * @param  list<array{label: string, value: string}>  $options
     * @param  array{placeholder?: string, rows?: int, step?: int|float, layout?: string, variant?: string}  $attributes
     * @param  array{required?: bool, min?: int|float, max?: int|float, max_size?: int, extensions?: list<string>, min_width?: int, min_height?: int, max_width?: int, max_height?: int}  $validation
     */
    public function __construct(
        public string $key,
        public string $label,
        public FieldInputType $input_type,
        public FieldValueType $value_type,
        public ?string $description = null,
        public array $options = [],
        public array $attributes = [],
        public array $validation = [],
        public mixed $default_value = null,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            key: $data['key'], label: $data['label'],
            input_type: FieldInputType::from($data['input_type']),
            value_type: FieldValueType::from($data['value_type']),
            description: $data['description'] ?? null,
            options: $data['options'] ?? [], attributes: $data['attributes'] ?? [],
            validation: $data['validation'] ?? [], default_value: $data['default_value'] ?? null,
        );
    }
}
