<?php

namespace Modules\Documents\Models;

use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Modules\Documents\Database\Factories\DocumentFactory;

class Document extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = ['category_id', 'file', 'file_media_id', 'status'];

    protected $attributes = ['status' => 'draft'];

    protected function casts(): array
    {
        return ['file_media_id' => 'integer', 'extra_fields' => 'array'];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(DocumentCategory::class, 'category_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(DocumentTranslation::class);
    }

    public function fileMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'file_media_id');
    }

    protected function fileUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $media = $this->fileMedia;
            $path = $media?->path ?? ($this->file_media_id ? null : $this->file);

            return $path ? Storage::disk($media?->disk ?? 'public')->url($path) : null;
        });
    }

    protected static function newFactory(): DocumentFactory
    {
        return DocumentFactory::new();
    }
}
