<?php

namespace App\Models\Settings;

use App\Models\Admin\Admin;
use App\Models\Media\MediaFile;
use Database\Factories\Settings\SettingValueFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['setting_definition_id', 'value', 'updated_by', 'media_id'])]
#[Hidden(['value'])]
class SettingValue extends Model
{
    /** @use HasFactory<SettingValueFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['value' => 'encrypted:array', 'media_id' => 'integer'];
    }

    public function definition(): BelongsTo
    {
        return $this->belongsTo(SettingDefinition::class, 'setting_definition_id');
    }

    public function media(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'media_id');
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }
}
