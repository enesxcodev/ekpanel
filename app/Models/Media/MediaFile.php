<?php

namespace App\Models\Media;

use App\Models\Admin\Admin;
use App\Models\Settings\SettingValue;
use Database\Factories\Media\MediaFileFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['folder_id', 'uploaded_by', 'disk', 'path', 'name', 'mime_type', 'extension', 'kind', 'size', 'width', 'height', 'alt_text'])]
class MediaFile extends Model
{
    /** @use HasFactory<MediaFileFactory> */
    use HasFactory, SoftDeletes;

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MediaFolder::class, 'folder_id');
    }

    public function favoritedBy(): BelongsToMany
    {
        return $this->belongsToMany(Admin::class, 'media_favorites');
    }

    public function settingValues(): HasMany
    {
        return $this->hasMany(SettingValue::class, 'media_id');
    }

    public function usages(): HasMany
    {
        return $this->hasMany(MediaUsage::class, 'media_id');
    }

    protected function casts(): array
    {
        return ['size' => 'integer', 'width' => 'integer', 'height' => 'integer'];
    }
}
