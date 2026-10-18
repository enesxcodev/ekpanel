<?php

namespace App\Enums\Fields;

enum FieldInputType: string
{
    case Text = 'text';
    case Textarea = 'textarea';
    case Email = 'email';
    case Number = 'number';
    case Range = 'range';
    case Boolean = 'boolean';
    case Select = 'select';
    case Radio = 'radio';
    case File = 'file';
    case Image = 'image';
    case Password = 'password';

    public function label(): string
    {
        return match ($this) {
            self::Text => 'Metin', self::Textarea => 'Uzun metin', self::Email => 'E-posta',
            self::Number => 'Sayı', self::Range => 'Aralık', self::Boolean => 'Açık / Kapalı',
            self::Select => 'Seçim listesi', self::Radio => 'Radio grubu', self::File => 'Dosya',
            self::Image => 'Görsel', self::Password => 'Şifre',
        };
    }

    public function isFile(): bool
    {
        return in_array($this, [self::File, self::Image], true);
    }

    public function hasOptions(): bool
    {
        return in_array($this, [self::Select, self::Radio], true);
    }

    /** @return list<FieldValueType> */
    public function valueTypes(): array
    {
        return match ($this) {
            self::Number, self::Range => [FieldValueType::Integer, FieldValueType::Float],
            self::Boolean => [FieldValueType::Boolean],
            self::File, self::Image => [FieldValueType::File, FieldValueType::Media],
            default => [FieldValueType::String],
        };
    }
}
