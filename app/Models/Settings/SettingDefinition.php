<?php

namespace App\Models\Settings;

use App\Enums\Settings\SettingInputType;
use App\Enums\Settings\SettingValueType;
use App\Services\Fields\FieldDefinition;
use Database\Factories\Settings\SettingDefinitionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['section_id', 'key', 'label', 'description', 'input_type', 'value_type', 'options', 'attributes', 'validation', 'default_value', 'is_sensitive', 'is_system', 'sort_order'])]
class SettingDefinition extends Model
{
    /** @use HasFactory<SettingDefinitionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'input_type' => SettingInputType::class, 'value_type' => SettingValueType::class,
            'options' => 'array', 'attributes' => 'array', 'validation' => 'array',
            'default_value' => 'json', 'is_sensitive' => 'boolean', 'is_system' => 'boolean',
        ];
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(SettingSection::class, 'section_id');
    }

    public function storedValue(): HasOne
    {
        return $this->hasOne(SettingValue::class);
    }

    public function effectiveValue(): mixed
    {
        if ($this->storedValue?->media_id !== null) {
            return $this->storedValue->media?->path;
        }

        return $this->storedValue === null ? $this->default_value : $this->storedValue->value['value'];
    }

    public function usesMediaLibrary(): bool
    {
        return $this->input_type === SettingInputType::Image && in_array($this->key, ['site.logo', 'site.footer_logo', 'site.favicon'], true);
    }

    public function hasValue(): bool
    {
        $value = $this->effectiveValue();

        return $value !== null && $value !== '';
    }

    public function fieldDefinition(): FieldDefinition
    {
        return new FieldDefinition(
            key: $this->key, label: $this->label,
            input_type: $this->input_type, value_type: $this->value_type,
            description: $this->description, options: $this->options ?? [],
            attributes: $this->getAttribute('attributes') ?? [], validation: $this->validation ?? [],
            default_value: $this->default_value,
        );
    }
}
