<?php

namespace App\Enums\Fields;

enum FieldValueType: string
{
    case String = 'string';
    case Integer = 'integer';
    case Float = 'float';
    case Boolean = 'boolean';
    case File = 'file';

    public function normalize(mixed $value): mixed
    {
        if ($value === null || ($value === '' && $this !== self::String)) {
            return null;
        }

        return match ($this) {
            self::Integer => (int) $value,
            self::Float => (float) $value,
            self::Boolean => (bool) $value,
            default => (string) $value,
        };
    }
}
