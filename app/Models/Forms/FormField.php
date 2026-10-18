<?php

namespace App\Models\Forms;

use App\Enums\Fields\FieldInputType;
use App\Enums\Fields\FieldValueType;
use App\Services\Fields\FieldDefinition;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FormField extends Model
{
    use HasFactory;

    protected $fillable = ['key', 'label', 'input_type', 'required', 'placeholder', 'description', 'width', 'css_class', 'input_class', 'role', 'options', 'validation', 'sort_order'];

    protected function casts(): array
    {
        return ['required' => 'boolean', 'options' => 'array', 'validation' => 'array'];
    }

    public function form(): BelongsTo
    {
        return $this->belongsTo(SiteForm::class, 'site_form_id');
    }

    public function definition(): FieldDefinition
    {
        return new FieldDefinition(
            key: $this->key, label: $this->label,
            input_type: FieldInputType::from($this->input_type === 'tel' ? 'text' : $this->input_type),
            value_type: match ($this->input_type) {
                'number' => FieldValueType::Float, 'boolean' => FieldValueType::Boolean, 'file' => FieldValueType::File, default => FieldValueType::String
            },
            options: $this->options ?? [],
            validation: ['required' => $this->required, ...($this->validation ?? [])],
        );
    }
}
