<?php

namespace Modules\Brand\Models;

use App\Models\Media\MediaFile;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Modules\Brand\Database\Factories\BrandFactory;

class Brand extends Model
{
    /** @use HasFactory<BrandFactory> */
    use HasFactory;

    protected $fillable = ['name', 'sort_order', 'is_active', 'image', 'image_media_id', 'short_description', 'link', 'extra_fields'];

    protected function casts(): array
    {
        return ['image_media_id' => 'integer', 'sort_order' => 'integer', 'is_active' => 'boolean', 'extra_fields' => 'array'];
    }

    public function imageMedia(): BelongsTo
    {
        return $this->belongsTo(MediaFile::class, 'image_media_id');
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(function (): ?string {
            $media = $this->imageMedia;
            $path = $media?->path ?? ($this->image_media_id ? null : $this->image);

            return $path ? Storage::disk($media?->disk ?? 'public')->url($path) : null;
        });
    }

    protected static function newFactory(): BrandFactory
    {
        return BrandFactory::new();
    }
}
