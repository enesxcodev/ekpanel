<?php

namespace App\Models\Settings;

use Database\Factories\Settings\SettingSectionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['key', 'label', 'sort_order'])]
class SettingSection extends Model
{
    /** @use HasFactory<SettingSectionFactory> */
    use HasFactory;

    public function definitions(): HasMany
    {
        return $this->hasMany(SettingDefinition::class, 'section_id')->orderBy('sort_order')->orderBy('id');
    }
}
